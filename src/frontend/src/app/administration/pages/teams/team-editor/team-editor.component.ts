import { Component, OnInit } from '@angular/core';
import { FormArray, FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { marker as _ } from '@biesbjerg/ngx-translate-extract-marker';
import { takeUntil } from 'rxjs/operators';
import { TEAM_MANAGER, TEAM_STANDARD } from 'src/app/administration/data/role.data';
import { Team } from 'src/app/administration/interfaces/team.interface';
import { RoleService } from 'src/app/administration/services/role.service';
import { TeamService } from 'src/app/administration/services/team.service';
import { UserService } from 'src/app/administration/services/user.service';
import { BaseSecurePageComponent } from 'src/app/shared/component/bases/base-secure-page.component';
import { CustomValidators } from 'src/app/shared/helpers/validators.helper';
import { BreadCrumb, FormDropdown, FormPageProps } from 'src/app/shared/interfaces/common.interface';
import { AlertsService } from 'src/app/shared/services/alerts/alerts.service';
import { AppRole } from 'src/app/starter/data/permissions.data';

@Component({
  selector: 'app-team-editor',
  templateUrl: './team-editor.component.html',
  styleUrls: ['./team-editor.component.scss'],
})
export class TeamEditorComponent extends BaseSecurePageComponent implements OnInit {
  edit = false;
  formGroup!: FormGroup;
  dataMembers?: any;
  team!: Team;
  description: any;

  id = this.activatedRoute.snapshot.params['id'];

  // FormDropdown
  users: FormDropdown[] = [];
  roles: FormDropdown[] = [
    { value: TEAM_MANAGER, description: 'team.' + TEAM_MANAGER },
    { value: TEAM_STANDARD, description: 'team.' + TEAM_STANDARD },
  ];

  // Breadcrumb for this page
  breadCrumbs: BreadCrumb[] = [
    {
      url: '/administration/teams',
      label: 'title.teams',
    },
    {
      url: '',
      label: this.id ? 'common.edit' : 'common.create',
    },
  ];

  // custom translation strings for specific form control errors
  errorTranslations = {
    user: [{ duplicated: 'error.field.name_must_be_unique' }],
  };

  // the page's state
  override pageState: FormPageProps = {
    state: 'loading',
    returnUrl: '/administration/teams',
  };

  /**
   * Get permission to create and edit the team page
   *
   * @return {string}
   */
  get pagePermissions(): string[] {
    return [AppRole.ROLE_ADMIN];
  }

  constructor(
    protected override roleService: RoleService,
    private activatedRoute: ActivatedRoute,
    private fb: FormBuilder,
    private userService: UserService,
    private teamService: TeamService,
    private alerts: AlertsService,
    private router: Router
  ) {
    super(roleService);
    this.initFormGroup();
  }

  onInit(): void {
    this.fetchUsers(() => {
      this.activatedRoute.params.pipe(takeUntil(this.onDestroy$)).subscribe({
        next: params => {
          setTimeout(() => {
            if (params['id']) {
              // EDIT mode
              this.initEditMode(params['id']);
            } else {
              // CREATE mode
              this.initCreateMode();
            }
          });
        },
        error: err => {
          console.log(err);
        },
      });
    });
  }

  /**
   * Setup form controls and initial validators
   */
  initFormGroup() {
    this.formGroup = this.fb.group({
      name: new FormControl('', [Validators.required]),
      members: this.fb.array([this.initMembers()], CustomValidators.hasDuplicate('key')),
    });
  }

  /**
   * Setup form member`s
   *
   * @param {any} data : member data
   * @returns
   */
  initMembers(data: any = null) {
    data = data || { key: null, role: null, description: null };
    return this.fb.group({
      key: new FormControl(data.key, [Validators.required]),
      role: new FormControl(data.role, [Validators.required]),
      description: [data.description],
    });
  }

  get members() {
    return (this.formGroup.controls['members'] as FormArray).controls as FormGroup<any>[];
  }

  get pushMembers() {
    return this.formGroup.get('members') as FormArray;
  }

  /**
   * Delete form member
   *
   * @param {number} idx : member id
   */
  onRemoveMember(idx: number) {
    this.pushMembers.removeAt(idx);
  }

  /**
   * Build dropdown data for `users` field
   *
   * @private
   */
  private fetchUsers(callback?: any) {
    this.userService
      .getUsersDropdown()
      .pipe(takeUntil(this.onDestroy$))
      .subscribe({
        next: users => {
          this.users = users;

          if (typeof callback === 'function') {
            callback();
          }
        },
        error: err => {
          console.log(err);
          this.users = [];
        },
      });
  }

  /**
   * Helper to init form controls in CREATE mode
   */
  initCreateMode() {
    this.formGroup.updateValueAndValidity();
    this.pageState.state = 'loaded';
  }

  /**
   * Helper to init form controls in Edit mode
   *
   * @param {number} id : Team id
   */
  initEditMode(id: number) {
    this.pushMembers.removeAt(0);
    this.edit = true;
    this.fetchModel(id);
  }

  /**
   * Fetch team data with the given id
   * @param {number} id : team id
   * @private
   */
  private fetchModel(id: number) {
    this.teamService.getTeamById(id).subscribe({
      next: res => {
        if (res) {
          this.team = res;
          this.formGroup.controls['name'].setValue(res.name);

          let members: any;
          this.team.userTeam.map(data => {
            this.users.forEach(res => {
              if (res.value == data.userId) {
                members = {
                  key: data.userId,
                  role: data.role,
                  description: res.description,
                };
                this.pushMembers.push(this.initMembers(members));
              }
            });
          });

          if (!members) this.pushMembers.push(this.initMembers());

          this.pageState.state = 'loaded';
        } else {
          this.pageState.state = 'error';
          this.pageState.message = _('error.msg.error_while_loading_team_data');
        }
      },
      error: err => {
        this.pageState.state = 'unauthorized';
        this.pageState.message = _('common.msg.unauthorized_page');
      },
    });
  }

  /**
   * Saves or updates team data
   */
  save() {
    const name = this.formGroup.controls['name'].value;
    this.dataMembers = this.formGroup.controls['members'].getRawValue().map((item: any) => ({
      userId: item.key,
      role: item.role,
    }));

    const role = this.dataMembers.filter((item: any) => item.role == TEAM_MANAGER);

    if (role.length > 0) {
      if (this.formGroup.valid) {
        let observable = this.edit
          ? this.teamService.updateTeam(this.team.id, name, this.dataMembers)
          : this.teamService.createTeam(name, this.dataMembers);

        observable.subscribe({
          next: res => {
            if (res && res.success) {
              this.router.navigate([`administration/teams`], {
                queryParams: this.queryParams,
              });
              this.alerts.setSuccess(_('team.msg.team_saved'));
            } else {
              this.alerts.setError(_('team.msg.team_save_error'));
            }
          },
          error: err => {
            this.alerts.setError(_('team.msg.team_save_error'));
          },
        });
      }
    } else {
      this.alerts.setError(_('team.msg.last_one'));
    }
  }
}

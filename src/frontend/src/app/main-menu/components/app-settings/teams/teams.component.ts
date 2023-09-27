import { Component, Input, OnInit } from '@angular/core';
import { FormArray, FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { marker as _ } from '@biesbjerg/ngx-translate-extract-marker';
import { AppService } from 'src/app/apps/services/apps/app.service';
import { BaseComponent } from 'src/app/shared/component/bases/base.component';
import { CustomValidators } from 'src/app/shared/helpers/validators.helper';
import { FormDropdown } from 'src/app/shared/interfaces/common.interface';
import { AlertsService } from 'src/app/shared/services/alerts/alerts.service';
import { App } from '../../../../apps/interfaces/app.interface';

@Component({
  selector: 'app-teams',
  templateUrl: './teams.component.html',
  styleUrls: ['./teams.component.scss'],
})
export class TeamsComponent extends BaseComponent implements OnInit {
  app!: App;
  teams: FormDropdown[] = [];
  description: any;
  formGroup!: FormGroup;

  // recive an app teams item
  @Input() appTeams: FormDropdown[] = [];

  // if set, this will be used to fetch teams data model for this component (and `edit` will be true)
  @Input() id: string | undefined;

  // if set, this will override the default disabled form when this true
  @Input() hasPermission: boolean = false;

  // custom translation strings for specific form control errors
  errorTranslations = {
    name: [{ duplicated: 'error.field.name_must_be_unique' }],
  };

  constructor(
    private fb: FormBuilder,
    private appService: AppService,
    private router: Router,
    private alerts: AlertsService
  ) {
    super();
  }

  ngOnInit(): void {
    this.teams = this.appTeams;

    this.fecthModel(this.id ?? '');

    this.formGroup = this.fb.group({
      teams: this.fb.array([this.initFormGroupTeams()], CustomValidators.hasDuplicate('key')),
    });
  }

  get formArrayTeams() {
    return (this.formGroup.controls['teams'] as FormArray).controls as FormGroup<any>[];
  }

  get pushTeam() {
    return this.formGroup.get('teams') as FormArray;
  }

  /**
   * Delete form field team
   *
   * @param {number} idx - team id
   */
  onRemoveTeam(idx: number) {
    this.pushTeam.removeAt(idx);
  }

  /**
   * Setup form team
   *
   * @param {any} data
   * @returns
   */
  initFormGroupTeams(data: any = null) {
    data = data || { key: null, desc: null };
    return this.fb.group({
      key: new FormControl({ value: data.key, disabled: !this.hasPermission }, [Validators.required]),
      desc: [data.desc],
    });
  }

  /**
   * Fetch team data with the given id
   *
   * @param {string} id
   * @private
   */
  private fecthModel(id: string) {
    this.appService.getAppById(id).subscribe({
      next: res => {
        if (res && res.success) {
          this.app = res.data;
          if (this.app.teamApp != null) {
            this.pushTeam.removeAt(0);
            this.app.teamApp.forEach(item => {
              this.pushTeam.push(this.initFormGroupTeams({ key: item.teamId, desc: item.teamName }));
            });
          }
        }
      },
      error: err => {
        console.error(err);
      },
    });
  }

  /**
   * Saves or updates team data
   */
  save() {
    const team = this.formGroup.controls['teams'].getRawValue().map((item: any) => ({
      teamId: item.key,
    }));

    const id = this.id ? this.id : '';

    if (this.formGroup.valid) {
      this.appService.updateAppTeams(id, team).subscribe({
        next: res => {
          if (res && res.success) {
            this.router.navigate([`/main-menu/apps/settings/${this.id}`]);
            this.alerts.setSuccess(_('app.msg.team_saved'));
          } else {
            this.alerts.setError(_('app.msg.team_save.error'));
          }
        },
        error: err => {
          this.alerts.setError(_('app.msg.team_save.error'));
        },
      });
    }
  }
}

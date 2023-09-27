import { Clipboard } from '@angular/cdk/clipboard';
import { Component, OnInit } from '@angular/core';
import { FormArray, FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { MatDialog } from '@angular/material/dialog';
import { ActivatedRoute, Router } from '@angular/router';
import { marker as _ } from '@biesbjerg/ngx-translate-extract-marker';
import 'hammerjs';
import { forkJoin } from 'rxjs';
import { takeUntil } from 'rxjs/operators';
import { RoleService } from 'src/app/administration/services/role.service';
import { TeamService } from 'src/app/administration/services/team.service';
import { AppSharedService } from 'src/app/main-menu/services/app-shared/app-shared.service';
import { MessageService } from 'src/app/messages/services/message/message.service';
import { BaseSecurePageComponent } from 'src/app/shared/component/bases/base-secure-page.component';
import { ConfirmDialogComponent } from 'src/app/shared/component/confirm-dialog/confirm-dialog.component';
import { CustomValidators } from 'src/app/shared/helpers/validators.helper';
import { BreadCrumb, FormDropdown } from 'src/app/shared/interfaces/common.interface';
import { Response } from 'src/app/shared/interfaces/response.interface';
import { AlertsService } from 'src/app/shared/services/alerts/alerts.service';
import { AppRole } from 'src/app/starter/data/permissions.data';
import { App, AppLogo, RequestApp } from '../../interfaces/app.interface';
import { AppService } from '../../services/apps/app.service';

@Component({
  selector: 'app-app-editor',
  templateUrl: './app-editor.component.html',
  styleUrls: ['./app-editor.component.scss'],
})
export class AppEditorComponent extends BaseSecurePageComponent implements OnInit {
  /** true in editing mode, false for new/create mode */
  edit: boolean = false;

  /** FormGroup  */
  formGroup!: FormGroup;

  /** only on EDIT mode: the app's id it defined from the {id} URL parameter */
  appId: string | null = null;

  /** only on EDIT mode: the fetched app data model from the `appId` */
  app?: App;

  /** only on EDIT mode: the app's logo */
  appLogo!: AppLogo;

  /** Holds currently selected help URL for the selected app tyle */
  appTypelink!: string;

  /** Holds current image of the image uploader */
  srcImage!: string;

  /** Options for teams dropdown */
  dropdownAppTeams: FormDropdown[] = [];

  /** Holds fetched data of available app types */
  appTypes: any = [];

  /** Dropdown data taken from `appTypes` */
  dropdownAppTypes: FormDropdown[] = [];

  // Breadcrumb for this page
  breadCrumbs: BreadCrumb[] = [];

  // custom translation strings for specific form control errors
  errorTranslations = {
    name: [{ duplicated: 'error.field.name_must_be_unique' }],
  };

  /**
   * Get permission to show the app create and update page
   *
   * @return {string[]}
   */
  get pagePermissions(): string[] {
    return this.appId ? [AppRole.ROLE_ADMIN, AppRole.ROLE_STANDARD] : [AppRole.ROLE_ADMIN];
  }

  get showMatBar(): boolean {
    return this.isCurrentUserHas([AppRole.ROLE_ADMIN]);
  }

  get isDataChangesAllowed(): boolean {
    return this.app ? this.app.isTeamManager == true : true;
  }

  get teamsFormArrayGroups() {
    return this.teamsFormArray.controls as FormGroup<any>[];
  }

  get teamsFormArray() {
    return this.formGroup.get('teams') as FormArray;
  }

  constructor(
    protected override roleService: RoleService,
    private fb: FormBuilder,
    private router: Router,
    private alerts: AlertsService,
    private appService: AppService,
    private activateRoute: ActivatedRoute,
    private clipboard: Clipboard,
    private dialog: MatDialog,
    private messageService: MessageService,
    private appSharedService: AppSharedService,
    private teamService: TeamService
  ) {
    super(roleService);
    this.appId = this.activateRoute.snapshot.paramMap.get('id');
    this.pageState.returnUrl = '/main-menu/apps';

    this.initBreadCrumbs();
    this.initQueryParams(router);
    this.initFormGroup();
  }

  private initBreadCrumbs() {
    this.breadCrumbs = [
      { url: '/main-menu/apps', label: 'title.apps' },
      { url: '', label: this.appId ? 'common.setting' : 'common.create' },
    ];
  }

  /**
   * Setup form controls and initial validators for CREATE mode
   *
   * @private
   */
  private initFormGroup() {
    this.formGroup = this.fb.group({
      name: ['', [Validators.required]],
      type: ['', [Validators.required]],
      description: ['', [Validators.required]],
      teams: this.fb.array([], CustomValidators.hasDuplicate('key')),
      logo: '',
    });
  }

  onInit(): void {
    // combine all needed API calls to populate form controls
    let apiCalls: any = {
      teams: this.teamService.getTeamsDropdown().pipe(takeUntil(this.onDestroy$)),
      appTypes: this.appService.getAllAppTypes().pipe(takeUntil(this.onDestroy$)),
    };

    if (this.appId != null) {
      apiCalls.model = this.appService.getAppById(this.appId).pipe(takeUntil(this.onDestroy$));
    }

    forkJoin(apiCalls).subscribe({
      next: (data: any) => {
        this.handleTeamData(data.teams);
        this.handleAppTypesData(data.appTypes);
        if (this.appId != null) {
          this.initEditMode(data.model);
        } else {
          this.initCreateMode();
        }
      },
      error: err => {
        console.log(err);
        this.dropdownAppTeams = [];
      },
    });
  }

  private handleTeamData(data: FormDropdown[] | undefined) {
    this.dropdownAppTeams = data ?? [];
  }

  private handleAppTypesData(res: Response | undefined) {
    if (res && res.data != null) {
      this.appTypes = res.data;
      this.appTypelink = res.data[0].url;

      this.dropdownAppTypes = this.appTypes.map(
        (res: any) =>
          ({
            value: res.type,
            description: 'type.' + res.type,
          } as FormDropdown)
      );
    } else {
      this.dropdownAppTypes = [];
    }
  }

  /**
   * Init form controls in CREATE mode
   */
  private initCreateMode() {
    this.pageState.state = 'loaded';
    this.teamsFormArray.push(this.initFormGroupTeams());
    this.formGroup.controls['teams'].setValidators(CustomValidators.hasDuplicate('key'));
    this.formGroup.controls['type'].setValue(this.appTypes[0].type);
    this.formGroup.updateValueAndValidity();
  }

  /**
   * Init form controls and class variables in EDIT mode
   */
  private initEditMode(res: Response | undefined) {
    if (res && res.success && res.data) {
      this.app = res.data;
      if (this.app == null) return;

      this.appLogo = this.appSharedService.getAppLogo(this.app.type, this.app.appLogo);

      this.formGroup.patchValue({
        name: this.app.name,
        type: this.app.type,
        description: this.app.description,
        logo: this.app.appLogo,
      });

      if (!this.app.isTeamManager) this.formGroup.disable();

      this.formGroup.controls['teams'].setValidators(null);
      this.formGroup.updateValueAndValidity();

      this.srcImage = this.app.appLogo;
      this.edit = true;
      this.pageState.state = 'loaded';
    } else {
      this.pageState.state = 'error';
      this.pageState.message = _('error.msg.error_while_loading_app_seting_data');
    }
  }

  /**
   * Setup FormGroup for each row of team
   *
   * @param {any} data
   */
  initFormGroupTeams(data: any = null) {
    data = data || { key: null, desc: null };
    return this.fb.group({
      key: new FormControl(data.key, [Validators.required]),
      desc: [data.desc],
    });
  }

  /**
   * Delete form field team
   *
   * @param {number} idx - team id
   */
  onRemoveTeam(idx: number) {
    this.teamsFormArray.removeAt(idx);
  }

  /**
   * Copy on clipboard API key
   */
  onCopy() {
    if (this.app && this.clipboard.copy(this.app?.apiKey)) {
      this.alerts.setSuccess(_('common.msg.copy_apikey'));
    }
  }

  /**
   * Confirm first before regenerating API Key
   */
  onGenerateAppApiKey() {
    this.dialog
      .open(ConfirmDialogComponent, {
        data: {
          title: 'common.confirmation',
          message: 'app.msg.generate_api_key',
        },
      })
      .afterClosed()
      .pipe(takeUntil(this.onDestroy$))
      .subscribe(res => {
        if (res) {
          this.regenerateApiKey();
        }
      });
  }

  /**
   * Regenerates API key
   */
  private regenerateApiKey() {
    if (this.app == null) return;

    this.appService.generateApiKey(this.app.id, this.app.apiKey).subscribe({
      next: res => {
        if (this.app && res && res.success) {
          this.app.apiKey = res.data.apiKey;
          this.router.navigate([`/main-menu/apps/settings/${res.data.id}`]);
          this.alerts.setSuccess(_('common.msg.updated_api_key'));
        } else {
          this.alerts.setError(_('common.msg.error.updated_api_key'));
        }
      },
    });
  }

  /**
   * Value changed event handler for app's type
   */
  onAppTypeChanged(value: string) {
    this.appTypes.find((item: any) => {
      if (value === item.type) {
        this.appTypelink = item.url;
      }
    });
  }

  /**
   * Build request payload for saving app data
   *
   * @returns {RequestApp}
   * @private
   */
  private buildAppRequest(): RequestApp {
    const team$ = this.formGroup.controls['teams'];
    const teams = team$
      ? team$.getRawValue().map((item: any) => ({
          teamId: item.key,
        }))
      : [];

    const imgLogo = document.getElementById('logo') as HTMLImageElement | any;

    this.formGroup.controls['logo'].setValue(imgLogo ? imgLogo.src : '');
    let logo = this.formGroup.controls['logo'].value;
    const splitLogo = logo.split(',');
    const srcImage = this.srcImage ? (splitLogo[1] ? true : false) : true;

    return {
      name: this.formGroup.controls['name'].value,
      description: this.formGroup.controls['description'].value,
      type: this.formGroup.controls['type'].value,
      team: this.edit ? undefined : teams,
      logo: splitLogo[1],
      updateLogo: this.edit ? !!srcImage : undefined,
    };
  }

  /**
   * Saves or updates app data
   */
  save() {
    if (this.formGroup.valid) {
      let observable =
        this.edit && this.app
          ? this.appService.updateApp(this.app.id, this.buildAppRequest())
          : this.appService.createApp(this.buildAppRequest());

      observable.subscribe({
        next: res => {
          if (res && res.success) {
            if (this.edit) this.messageService.refreshMessage();

            this.app = res.data;
            if (this.app == null) {
              this.alerts.setError(_('app.msg.app_saved.error'));
              return;
            }

            this.srcImage = this.app.appLogo;
            this.appLogo = this.appSharedService.getAppLogo(this.app.type, this.app.appLogo);

            this.router.navigate([`/main-menu/apps/settings/${this.app.id}`], {
              queryParams: this.queryParams,
            });
            this.alerts.setSuccess(_('app.msg.app_saved'));
          } else {
            this.alerts.setError(_('app.msg.app_saved.error'));
          }
        },
        error: err => {
          this.alerts.setError(_('app.msg.app_saved.error'));
        },
      });
    }
  }

  /**
   * To Delete App Logo
   */
  onDeleteLogo(): void {
    const dialogRef = this.dialog.open(ConfirmDialogComponent, {
      data: {
        title: _('common.confirmation'),
        message: _('common.msg.delete.logo'),
      },
    });
    dialogRef
      .afterClosed()
      .pipe(takeUntil(this.onDestroy$))
      .subscribe((confirm: boolean) => {
        if (confirm) {
          this.srcImage = '';
        }
      });
  }
}

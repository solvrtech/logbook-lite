import { Component, Input, OnInit } from '@angular/core';
import { FormArray, FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { MatDialog } from '@angular/material/dialog';
import { ActivatedRoute, Router } from '@angular/router';
import { marker as _ } from '@biesbjerg/ngx-translate-extract-marker';
import { takeUntil } from 'rxjs/operators';
import { RoleService } from 'src/app/administration/services/role.service';
import { AppAlert, ConfigHealth, ConfigLog, CreateAlert } from 'src/app/apps/interfaces/alert.interface';
import { AppAlertService } from 'src/app/apps/services/app-alerts/app-alert.service';
import { AppService } from 'src/app/apps/services/apps/app.service';
import { HealthCheckDialogComponent } from 'src/app/main-menu/components/dialogs/health-check-dialog/health-check-dialog.component';
import {
  DROPDOWN_CHECKKEYS,
  DROPDOWN_NOTIFIES,
  DROPDOWN_SEVERITIES,
  DROPDOWN_SOURCES,
} from 'src/app/main-menu/data/dropdown-config.data';
import {
  CACHE,
  CPU_LOAD,
  DATABASE,
  DATABASE_SIZE,
  LAST_15_MINUTES,
  LAST_5_MINUTES,
  LAST_MINUTE,
  MEMORY,
  MEMORY_USAGE,
  REDIS_SIZE,
  STATUS,
  USED_DISK,
  USED_DISK_SPACE,
} from 'src/app/main-menu/data/specific.data';
import { BaseSecurePageComponent } from 'src/app/shared/component/bases/base-secure-page.component';
import { CustomValidators } from 'src/app/shared/helpers/validators.helper';
import { BreadCrumb, FormDropdown, FormPageProps } from 'src/app/shared/interfaces/common.interface';
import { AlertsService } from 'src/app/shared/services/alerts/alerts.service';
import { AppRole } from 'src/app/starter/data/permissions.data';

@Component({
  selector: 'app-alert-editor',
  templateUrl: './alert-editor.component.html',
  styleUrls: ['./alert-editor.component.scss'],
})
export class AlertEditorComponent extends BaseSecurePageComponent implements OnInit {
  //if set, this will override default page's title
  @Input() title!: string;

  //if set ,this will override default page's breadcrumb
  @Input() breadCrumbs!: BreadCrumb[];

  //if set, this will be used to fetch alert data model for this page (and `edit` will be true)
  @Input() alertId!: number;

  formGroup!: FormGroup;
  appId = this.activatedRoute.snapshot.params['id'];
  level = [];
  editLevel = [];
  edit: boolean = false;
  appAlert!: AppAlert;
  hasAccess!: boolean;

  // custom translation strings for specific form control errors
  errorTranslations = {
    name: [{ duplicated: 'error.field.name_must_be_unique' }],
  };

  sources: FormDropdown[] = DROPDOWN_SOURCES;
  severities: FormDropdown[] = DROPDOWN_SEVERITIES;
  notifies: FormDropdown[] = DROPDOWN_NOTIFIES;
  checkKeys: FormDropdown[] = DROPDOWN_CHECKKEYS;

  items: any[] = [];

  // Breadcrumb for this page
  pageBreadCrumbs: BreadCrumb[] = [
    {
      url: '/main-menu/apps',
      label: 'title.apps',
    },
    {
      url: `/main-menu/apps/alert/${this.appId}`,
      label: 'common.alerts',
    },
    {
      url: '',
      label: this.activatedRoute.snapshot.params['alertId'] ? 'common.edit' : 'common.create',
    },
  ];

  /**
   * Get permission to show the alert create and update page
   *
   * @return {string}
   */
  get pagePermissions(): string[] {
    return [AppRole.ROLE_ADMIN, AppRole.ROLE_STANDARD];
  }

  // the page's state
  override pageState: FormPageProps = {
    state: 'loading',
    returnUrl: `/main-menu/apps/alert/${this.appId}`,
  };

  constructor(
    protected override roleService: RoleService,
    private activatedRoute: ActivatedRoute,
    private fb: FormBuilder,
    private appAlertService: AppAlertService,
    private alerts: AlertsService,
    private router: Router,
    private appService: AppService,
    private dialog: MatDialog
  ) {
    super(roleService);

    this.initFormGroup();
  }

  onInit(): void {
    if (this.alertId != null) {
      this.initEditMode(this.alertId);
    } else {
      this.activatedRoute.params.pipe(takeUntil(this.onDestroy$)).subscribe(params => {
        setTimeout(() => {
          if (params['alertId']) {
            this.initEditMode(params['alertId']);
          } else {
            this.initCreateMode();
          }
        });
      });
    }
  }

  /**
   * Setup form controls and initial validators
   *
   * @private
   */
  private initFormGroup() {
    this.formGroup = this.fb.group({
      name: new FormControl('', [Validators.required, Validators.maxLength(255)]),
      active: new FormControl(''),
      source: new FormControl('', [Validators.required]),
      severity: new FormControl('', []),
      howManyTimes: new FormControl('', []),
      duration: new FormControl('', [Validators.required]),
      message: new FormControl(''),
      stacktrace: new FormControl(''),
      browser: new FormControl(''),
      os: new FormControl(''),
      device: new FormControl(''),
      additional: new FormControl(''),
      notify: new FormControl('', [Validators.required]),
      useNotifyLimit: new FormControl(''),
      notifyLimit: new FormControl('', []),
      period: new FormControl({ value: '', disabled: true }),
      specifics: this.fb.array([], []),
    });
  }

  get specifics() {
    return (this.formGroup.controls['specifics'] as FormArray).controls as FormGroup<any>[];
  }

  get specificPush() {
    return this.formGroup.get('specifics') as FormArray;
  }

  addSpecifics(data: any = null) {
    data = data || { key: null, item: null, value: null };

    let value = new FormControl(data.value, [Validators.required, Validators.pattern('^[0-9]*$'), Validators.min(0)]);
    if (data.value == 'failed') {
      value = new FormControl({ value: data.value, disabled: true }, [Validators.required]);
    }

    if (data.item == MEMORY_USAGE) {
      value = new FormControl(data.value, [
        Validators.required,
        Validators.pattern('^[0-9]*$'),
        Validators.min(0),
        Validators.max(100),
      ]);
    }
    return this.fb.group({
      key: new FormControl(data.key, [Validators.required]),
      item: new FormControl(data.item, [Validators.required]),
      value: value,
    });
  }

  /**
   * Delete form field specific
   *
   * @param {number} idx - specific id
   */
  removeSpecific(idx: number) {
    this.specificPush.removeAt(idx);
  }

  /**
   * Build request payload for saving alert app data
   *
   * @return {CreateAlert}
   * @private
   */
  private buildAlertRequest(): CreateAlert {
    return {
      name: this.formGroup.controls['name'].value,
      active: !!this.formGroup.controls['active'].value,
      source: this.formGroup.controls['source'].value,
      notifyTo: this.formGroup.controls['notify'].value,
      restrictNotify: !!this.formGroup.controls['useNotifyLimit'].value,
      notifyLimit: this.formGroup.controls['notifyLimit'].value ? this.formGroup.controls['notifyLimit'].value : 0,
      config: this.formGroup.controls['source'].value == 'log' ? this.configLog() : this.configHealth(),
    };
  }

  /**
   * Build request payload if the selected `source` is `log`
   *
   * @returns {ConfigLog} configLog
   */
  private configLog(): ConfigLog {
    const level = this.formGroup.controls['severity'].value;
    const source = this.formGroup.controls['source'].value;

    level
      ? level.forEach((res: any) => {
          if (res.value) {
            this.level = level ? level.map((res: any) => ({ level: res.value })) : [];
          } else {
            this.level = level ? level.map((res: any) => ({ level: res })) : [];
          }
        })
      : [];

    return {
      level: source == 'health' ? [] : this.level,
      manyFailures: this.formGroup.controls['howManyTimes'].value
        ? Number(this.formGroup.controls['howManyTimes'].value)
        : 0,
      duration: this.formGroup.controls['duration'].value ? Number(this.formGroup.controls['duration'].value) : 0,
      message: this.formGroup.controls['message'].value,
      stackTrace: this.formGroup.controls['stacktrace'].value,
      browser: this.formGroup.controls['browser'].value,
      os: this.formGroup.controls['os'].value,
      device: this.formGroup.controls['device'].value,
      additional: this.formGroup.controls['additional'].value,
    };
  }

  /**
   * Build request payload if the selected `source` is `health`
   *
   * @returns {ConfigHealth} configHealth
   */
  private configHealth(): ConfigHealth {
    const specifics = this.formGroup.controls['specifics'].getRawValue().map((item: any) => ({
      checkKey: item.key,
      item: item.item,
      value: item.value,
    }));

    return {
      manyFailures: this.formGroup.controls['howManyTimes'].value
        ? Number(this.formGroup.controls['howManyTimes'].value)
        : 0,
      specific: specifics,
    };
  }

  /**
   * Helper to init form controls in CREATE mode
   *
   * @private
   */
  private initCreateMode() {
    this.formGroup.updateValueAndValidity();
    this.fetchModelAppUserStandard();
  }

  /**
   * Helper to init form controls in EDIT mode
   *
   * @param {number} id : alert id
   */
  private initEditMode(id: number) {
    this.edit = true;

    if (this.alertId != null) {
      this.hasAccess = true;
      this.formGroup.disable();
    }

    this.fetchModelAppUserStandard(id);
  }

  /**
   * Fetch app data with the given id
   *
   * @param {number|null} appAlertId
   * @private
   */
  private fetchModelAppUserStandard(appAlertId: number | null = null) {
    this.appService.getAppUserStandard(this.appId).subscribe({
      next: res => {
        if (res && res.success) {
          if (res.data.isTeamManager) {
            this.pageState.state = 'loaded';
            this.formGroup.controls['period'].setValue(
              res.data.appHealthSetting ? res.data.appHealthSetting.period : ''
            );

            if (appAlertId) this.fetchModel(appAlertId);
          } else {
            this.pageState.state = 'unauthorized';
            this.pageState.message = _('common.msg.unauthorized_page');
          }
        } else {
          this.pageState.state = 'error';
          this.pageState.message = _('error.msg.error_while_loading_alert_data');
        }
      },
      error: err => {
        this.pageState.state = 'unauthorized';
        this.pageState.message = _('common.msg.unauthorized_page');
      },
    });
  }

  /**
   * Fetch alert data with the given id
   *
   * @param {number} id : alert id
   * @private
   */
  private fetchModel(id: number) {
    this.appAlertService.getAppAlertById(this.appId, id).subscribe({
      next: res => {
        this.appAlert = res;
        const level = this.appAlert.config.level;
        level
          ? level.forEach((res: any) => {
              if (res.level) {
                this.editLevel = level ? level.map((res: any) => ({ value: res.level })) : [];
              } else {
                this.editLevel = level ? level.map((res: any) => ({ value: res })) : [];
              }
            })
          : [];

        this.specificPush.removeAt(0);
        if (this.appAlert?.config.specific != null) {
          this.appAlert.config.specific.forEach((item: any) => {
            this.specificPush.push(this.addSpecifics({ key: item.checkKey, item: item.item, value: item.value }));
          });
        }

        this.formGroup.patchValue({
          name: this.appAlert.name ? this.appAlert.name : '',
          active: this.appAlert.active ? this.appAlert.active : false,
          source: this.appAlert.source ? this.appAlert.source : '',
          severity: this.editLevel,
          howManyTimes: this.appAlert.config.manyFailures ? this.appAlert.config.manyFailures : null,
          duration: this.appAlert.config.duration ? this.appAlert.config.duration : '',
          message: this.appAlert.config.message ? this.appAlert.config.message : '',
          stacktrace: this.appAlert.config.stackTrace ? this.appAlert.config.stackTrace : '',
          browser: this.appAlert.config.browser ? this.appAlert.config.browser : '',
          os: this.appAlert.config.os ? this.appAlert.config.os : '',
          device: this.appAlert.config.device ? this.appAlert.config.device : '',
          additional: this.appAlert.config.additional ? this.appAlert.config.additional : '',
          notify: this.appAlert.notifyTo ? this.appAlert.notifyTo : '',
          useNotifyLimit: this.appAlert.restrictNotify ? this.appAlert.restrictNotify : false,
          notifyLimit: this.appAlert.notifyLimit ? this.appAlert.notifyLimit : null,
          period: '',
        });
      },
      error: () => {
        this.pageState.state = 'error';
        this.pageState.message = _('error.msg.error_while_loading_alert_data');
      },
    });
  }

  /**
   * Event handler for `change severity` select
   * @param {boolean} toChange enabled or disabled state
   */
  onChangeSeverity(toChange: string) {
    toChange == 'health' ? this.disableSeverity() : this.enableSeverity();
  }

  /**
   * Enables needed validators for `severity` and `duration`
   *
   * @private
   */
  private enableSeverity() {
    this.formGroup.controls['severity'].setValidators([Validators.required]);
    this.formGroup.controls['duration'].setValidators([Validators.required]);

    this.formGroup.updateValueAndValidity();
  }

  /**
   * Disables validators for `severity` and `duration`
   *
   * @private
   */
  private disableSeverity() {
    this.formGroup.controls['severity'].setValidators(null);
    this.formGroup.controls['severity'].setValue(null);
    this.formGroup.controls['duration'].setValidators(null);
    this.formGroup.controls['duration'].setValue('');
    this.formGroup.controls['specifics'].setValidators([CustomValidators.hasDuplicate('key')]);

    this.formGroup.updateValueAndValidity();
  }

  /**
   * Event handler for `change notify limit` toggle
   * @param {boolean} toChange enabled or disabled state
   */
  onChangeNotifyLimit(toChange: boolean) {
    toChange ? this.enableNotifyLimit() : this.disableNotifyLimit();
  }

  /**
   * Enables needed validators for `notifyLimit`
   *
   * @private
   */
  private enableNotifyLimit() {
    this.formGroup.controls['notifyLimit'].setValidators([Validators.required]);
    this.formGroup.controls['notifyLimit'].setValue(
      this.appAlert && this.appAlert.notifyLimit ? this.appAlert.notifyLimit : null
    );
    this.formGroup.updateValueAndValidity();
  }

  /**
   * Disables validators for `notifyLimit`
   *
   * @private
   */
  private disableNotifyLimit() {
    this.formGroup.controls['notifyLimit'].setValidators(null);
    this.formGroup.controls['notifyLimit'].setValue(null);

    this.formGroup.updateValueAndValidity();
  }

  /**
   * Set select form field `item`
   *
   * @param {string} event
   * @param {number} idx
   */
  onChangeItem(event: string, idx: number) {
    if (event == CACHE) {
      this.items[idx] = [{ value: STATUS, description: 'common.status' }];
    } else if (event == CPU_LOAD) {
      this.items[idx] = [
        { value: STATUS, description: 'common.status' },
        { value: LAST_MINUTE, description: 'health.lastMinute' },
        { value: LAST_5_MINUTES, description: 'health.last5Minutes' },
        { value: LAST_15_MINUTES, description: 'health.last15Minutes' },
      ];
    } else if (event == DATABASE) {
      this.items[idx] = [
        { value: STATUS, description: 'common.status' },
        { value: DATABASE_SIZE, description: 'health.databaseSize' },
      ];
    } else if (event == MEMORY) {
      this.items[idx] = [
        { value: STATUS, description: 'common.status' },
        { value: MEMORY_USAGE, description: 'health.memoryUsage' },
      ];
    } else if (event == USED_DISK) {
      this.items[idx] = [
        { value: STATUS, description: 'common.status' },
        { value: USED_DISK_SPACE, description: 'health.usedDiskSpace' },
      ];
    } else {
      this.items[idx] = [];
    }
  }

  /**
   * If the form field selects `status` so form field `value`
   * set the value `failed` and disabled the form
   *
   * @param {any} event
   * @param {number} idx
   */
  onChangeStatus(event: any, idx: number) {
    if (event.value == STATUS) {
      (this.specificPush.at(idx) as FormGroup).controls['value'].disable();
      (this.specificPush.at(idx) as FormGroup).controls['value'].setValue('failed');
    } else {
      (this.specificPush.at(idx) as FormGroup).controls['value'].setValue('');
      (this.specificPush.at(idx) as FormGroup).controls['value'].enable();
    }
  }

  /**
   * Set `mat-hint` if `value` not equal to `status`
   *
   * @param {any} control
   * @param {number} idx
   * @returns
   */
  hintValue(control: any, idx: number) {
    let hint: any[] = [];
    if (control == USED_DISK_SPACE) {
      hint[idx] = ['in percentage'];
    } else if (control == DATABASE_SIZE || control == MEMORY_USAGE || control == REDIS_SIZE) {
      hint[idx] = ['in Mega Bytes (MB)'];
    } else if (control == LAST_MINUTE || control == LAST_5_MINUTES || control == LAST_15_MINUTES) {
      hint[idx] = ['in floating number'];
    } else {
      hint[idx] = [];
    }
    return hint[idx];
  }

  /**
   * Set error form `value` if form `item`== MEMORY_USAGE and `value` > 100
   *
   * @param {any} control
   * @param {number} idx
   * @param {any} event
   */
  onChangeValue(control: any, idx: number, event: any) {
    if (control == MEMORY_USAGE) {
      if (event.target.value > 100) {
        (this.specificPush.at(idx) as FormGroup).controls['value'].setErrors({ biggest: true });
      }
    }
  }

  infoHealthCheck(event: any) {
    this.dialog.open(HealthCheckDialogComponent, { data: { title: 'app.alert.health_check' } });
  }

  /**
   * Saves or updates alert data
   */
  save() {
    if (this.formGroup.valid) {
      let observable = this.edit
        ? this.appAlertService.updateAppAlert(this.appId, this.appAlert.id, this.buildAlertRequest())
        : this.appAlertService.createAppAlert(this.appId, this.buildAlertRequest());

      observable.subscribe({
        next: res => {
          if (res && res.success) {
            this.router.navigate([`/main-menu/apps/alert/${this.appId}`]);
            this.alerts.setSuccess(_('app.alert.msg.alert_saved'));
          } else {
            this.alerts.setError(_('app.alert.msg.alert_saved.error'));
          }
        },
        error: err => {
          this.alerts.setError(_('app.alert.msg.alert_saved.error'));
        },
      });
    }
  }
}

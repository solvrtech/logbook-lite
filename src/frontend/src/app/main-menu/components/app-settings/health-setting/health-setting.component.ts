import { Component, Input, OnInit } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { marker as _ } from '@biesbjerg/ngx-translate-extract-marker';
import { AppService } from 'src/app/apps/services/apps/app.service';
import { BaseComponent } from 'src/app/shared/component/bases/base.component';
import { CustomValidators } from 'src/app/shared/helpers/validators.helper';
import { AlertsService } from 'src/app/shared/services/alerts/alerts.service';
import { environment } from 'src/environments/environment';
import { App, AppHealthSetting } from '../../../../apps/interfaces/app.interface';

@Component({
  selector: 'app-health-setting',
  templateUrl: './health-setting.component.html',
  styleUrls: ['./health-setting.component.scss'],
})
export class HealthSettingComponent extends BaseComponent implements OnInit {
  /** FormGroup */
  formGroup!: FormGroup;

  // Link URL `read here` subtitle for health setting
  link = environment.healthStatusLink;

  // custom translation strings for specific form control errors
  errorTranslations = {
    url: [{ invalidUrl: 'errors.fields.invalid_url' }],
  };

  // max value request period
  maxRequestPeriod: number = 259200;

  // min value request period
  minRequestPeriod: number = 5;

  // if set, this will be used to the App data model for this page (and `show` will be true)
  @Input() app!: App;

  constructor(
    private fb: FormBuilder,
    private appService: AppService,
    private alerts: AlertsService,
    private router: Router
  ) {
    super();
    this.initFormGroup();
  }

  ngOnInit(): void {
    if (this.app != null) this.initHealthSetting();
  }

  /**
   * Setup form controls and initial validators
   */
  private initFormGroup() {
    this.formGroup = this.fb.group({
      enable: new FormControl(''),
      baseUrl: new FormControl('', [Validators.required, CustomValidators.urlFormat]),
      requestPeriod: new FormControl('', [
        Validators.required,
        Validators.pattern('^[0-9]*$'),
        Validators.min(this.minRequestPeriod),
        Validators.max(this.maxRequestPeriod),
      ]),
    });
  }

  /**
   * init form controls
   */
  private initHealthSetting() {
    this.formGroup.patchValue({
      enable: this.app.appHealthSetting ? this.app.appHealthSetting.isEnabled : false,
      baseUrl: this.app.appHealthSetting ? this.app.appHealthSetting.url : '',
      requestPeriod: this.app.appHealthSetting ? this.app.appHealthSetting.period : '',
    });

    if (!this.app.isTeamManager) this.formGroup.disable();
  }

  /**
   * Build request payload for saving app health setting data
   *
   * @return {AppHealthSetting} AppHealthSetting
   */
  private buildHealthSettingRequest(): AppHealthSetting {
    return {
      isEnabled: this.formGroup.controls['enable'].value,
      period: this.formGroup.controls['requestPeriod'].value,
      url: this.formGroup.controls['baseUrl'].value,
    };
  }

  /**
   * Save health setting data
   */
  save() {
    if (this.formGroup.valid) {
      this.appService.appHealthSetting(this.app.id, this.buildHealthSettingRequest()).subscribe({
        next: res => {
          if (res && res.success) {
            this.router.navigate([`/main-menu/apps/settings/${this.app.id}`]);
            this.alerts.setSuccess(_('app.msg.health_status_saved'));
          } else {
            this.alerts.setError(_('app.msg.health_status_saved.error'));
          }
        },
        error: err => {
          this.alerts.setError(_('app.msg.health_status_saved.error'));
        },
      });
    }
  }
}

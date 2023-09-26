import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { MatDialog } from '@angular/material/dialog';
import { Router } from '@angular/router';
import { marker as _ } from '@biesbjerg/ngx-translate-extract-marker';
import { takeUntil } from 'rxjs';
import { BaseComponent } from 'src/app/shared/component/bases/base.component';
import { ConfirmDialogComponent } from 'src/app/shared/component/confirm-dialog/confirm-dialog.component';
import { AlertsService } from 'src/app/shared/services/alerts/alerts.service';
import { SettingSecurity } from '../../interfaces/setting.interface';
import { SettingService } from '../../services/settings/setting.service';

@Component({
  selector: 'app-security-settings',
  templateUrl: './security-settings.component.html',
  styleUrls: ['./security-settings.component.scss'],
})
export class SecuritySettingsComponent extends BaseComponent implements OnInit {
  formGroup!: FormGroup;
  mfaAuthentication: boolean = false;

  constructor(
    private fb: FormBuilder,
    private settingService: SettingService,
    private router: Router,
    private alerts: AlertsService,
    private dialog: MatDialog
  ) {
    super();

    this.initFormGroup();
  }

  ngOnInit(): void {
    this.initSmtpSetting();
    this.initSecuritySetting();
  }

  private initSmtpSetting() {
    this.settingService
      .listenOnSmtpSetting()
      .pipe(takeUntil(this.onDestroy$))
      .subscribe(mailSetting => {
        if (mailSetting) {
          this.formGroup.controls['enabled'].enable();
        } else {
          this.formGroup.controls['enabled'].disable();
        }
      });
  }

  /**
   * Setup form controls and initial validators
   *
   * @private
   */
  private initFormGroup() {
    this.formGroup = this.fb.group({
      enabled: new FormControl(''),
      delay: new FormControl('', [Validators.required]),
      max_failed: new FormControl('', [Validators.required]),
      max_resend: new FormControl('', [Validators.required]),
      waiting_time: new FormControl('', [Validators.required]),
      max_login: new FormControl('', [Validators.required]),
    });
  }

  /**
   * Event handler for `change delay, max failed, and max resend` toggle
   * @param toChange enabled or disabled state
   */
  onChange(toChange: boolean) {
    toChange ? this.enableValidator() : this.disableValidator();
  }

  /**
   * Enables needed validators for `delay`, `max_failed`, `max_resend`, and FormGroup
   *
   * @private
   */
  private enableValidator() {
    this.formGroup.controls['delay'].setValidators(Validators.required);
    this.formGroup.controls['max_failed'].setValidators(Validators.required);
    this.formGroup.controls['max_resend'].setValidators(Validators.required);
    this.formGroup.updateValueAndValidity();
  }

  /**
   * Disables validators for `delay`, `max_failed`, `max_resend`, and FormGroup
   *
   * @private
   */
  private disableValidator() {
    this.formGroup.controls['delay'].setValidators(null);
    this.formGroup.controls['max_failed'].setValidators(null);
    this.formGroup.controls['max_resend'].setValidators(null);
    this.formGroup.controls['delay'].setValue('');
    this.formGroup.controls['max_failed'].setValue('');
    this.formGroup.controls['max_resend'].setValue('');
    this.formGroup.updateValueAndValidity();
  }

  /**
   * Build request payload for saving setting security data
   *
   * @return {settingSecurity} settingSecurity
   * @private
   */
  private buildSecurityRequest(): SettingSecurity {
    const enabled = this.formGroup.controls['enabled'].value;

    return {
      mfaAuthentication: !!this.formGroup.controls['enabled'].value,
      mfaDelayResend: enabled ? this.formGroup.controls['delay'].value : null,
      mfaMaxResend: enabled ? this.formGroup.controls['max_resend'].value : null,
      mfaMaxFailed: enabled ? this.formGroup.controls['max_failed'].value : null,
      loginInterval: this.formGroup.controls['waiting_time'].value,
      loginMaxFailed: this.formGroup.controls['max_login'].value,
    };
  }

  /**
   * Fetch setting security data
   *
   * @private
   */
  private initSecuritySetting() {
    this.settingService.getSecurity().subscribe({
      next: res => {
        if (res && res.success) {
          this.mfaAuthentication = res.data?.mfaAuthentication ? res.data.mfaAuthentication : false;

          this.formGroup.setValue({
            enabled: res.data?.mfaAuthentication ? res.data.mfaAuthentication : false,
            delay: res.data?.mfaDelayResend ? res.data.mfaDelayResend : '',
            max_resend: res.data?.mfaMaxResend ? res.data.mfaMaxResend : '',
            max_failed: res.data?.mfaMaxFailed ? res.data.mfaMaxFailed : '',
            waiting_time: res.data?.loginInterval ? res.data.loginInterval : '',
            max_login: res.data?.loginMaxFailed ? res.data.loginMaxFailed : '',
          });
        }
      },
      error: err => {
        console.log(err);
      },
    });
  }

  /**
   * Save or update setting security data
   *
   * @private
   */
  private saveSettingSecurity() {
    this.settingService.updateSecurity(this.buildSecurityRequest()).subscribe({
      next: res => {
        if (res && res.success) {
          this.initSecuritySetting();
          this.router.navigate(['/administration/settings']);
          this.alerts.setSuccess(_('setting.msg.security_setting_saved'));
        } else {
          this.alerts.setError(_('setting.msg.security_setting_save.error'));
        }
      },
      error: () => {
        this.alerts.setError(_('setting.msg.security_setting_save.error'));
      },
    });
  }

  /**
   * Show a confirmation message when enable-two-factor-auth
   * is disabled from being enabled previously
   */
  save() {
    if (this.formGroup.valid) {
      const enabled = !!this.formGroup.controls['enabled'].value;

      if (!enabled) {
        if (this.mfaAuthentication) {
          this.dialog
            .open(ConfirmDialogComponent, {
              data: {
                title: 'common.confirmation',
                message: 'setting.msg.enable_two_fa_authenticator',
              },
              maxWidth: '420px',
            })
            .afterClosed()
            .pipe(takeUntil(this.onDestroy$))
            .subscribe({
              next: res => {
                if (res) {
                  this.saveSettingSecurity();
                }
              },
              error: err => {
                this.alerts.setError(_('common.msg.something_went_wrong'));
              },
            });
        } else this.saveSettingSecurity();
      } else {
        this.saveSettingSecurity();
      }
    }
  }
}

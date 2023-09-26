import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { SettingService } from 'src/app/administration/services/settings/setting.service';
import { BasePageComponent } from 'src/app/shared/component/bases/base-page.component';
import { PageMode } from 'src/app/shared/interfaces/common.interface';
import { AlertsService } from 'src/app/shared/services/alerts/alerts.service';
import { environment } from 'src/environments/environment';
import { AuthService } from '../../services/auth/auth.service';

@Component({
  selector: 'app-reset-password',
  templateUrl: './reset-password.component.html',
  styleUrls: ['./reset-password.component.scss'],
})
export class ResetPasswordComponent extends BasePageComponent implements OnInit {
  formGroup!: FormGroup;
  mode: PageMode = 'READY';

  // settings
  subtitle: string = '';
  languages: any = environment.languageCodes;
  defaultLanguage: string = 'en';
  languageCodes = environment.languageCodes;

  constructor(
    private fb: FormBuilder,
    private alerts: AlertsService,
    private authService: AuthService,
    private settingService: SettingService
  ) {
    super();
  }

  ngOnInit(): void {
    this.fetchSetting();

    this.defaultLanguage = this.settingService.currentLanguage
      ? this.settingService.currentLanguage.defaultLanguage ?? 'en'
      : 'en';

    // Setup form controls and initial validator
    this.formGroup = this.fb.group({
      email: new FormControl('', [Validators.required, Validators.email]),
    });
  }

  /**
   * Fecth setting data
   *
   * @private
   */
  private fetchSetting() {
    this.settingService.getSettingsAll().subscribe({
      next: res => {
        if (res && res.success) {
          if (res.data) {
            // settings
            this.subtitle = res.data.generalSetting ? res.data.generalSetting.applicationSubtitle ?? '' : '';
            this.defaultLanguage = res.data.generalSetting ? res.data.generalSetting.defaultLanguage ?? 'en' : 'en';
            this.languages = res.data.generalSetting
              ? res.data.generalSetting.languagePreference ?? this.languageCodes
              : this.languageCodes;
          }
        }
      },
    });
  }

  /**
   * Resend email for reset password
   */
  resetPassword() {
    const email = this.formGroup.controls['email'].value;

    if (this.formGroup.valid) {
      this.authService.resetPassword(email).subscribe({
        next: res => {
          if (res && res.success) {
            this.mode = 'FINISHED';
          }
        },
        error: () => this.alerts.setError('reset.msg.invalid_username_email'),
      });
    }
  }
}

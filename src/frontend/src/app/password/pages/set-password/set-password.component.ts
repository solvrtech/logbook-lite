import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { takeUntil } from 'rxjs/operators';
import { SettingService } from 'src/app/administration/services/settings/setting.service';
import { BasePageComponent } from 'src/app/shared/component/bases/base-page.component';
import { CustomValidators } from 'src/app/shared/helpers/validators.helper';
import { PageMode } from 'src/app/shared/interfaces/common.interface';
import { AlertsService } from 'src/app/shared/services/alerts/alerts.service';
import { environment } from 'src/environments/environment';
import { PasswordService } from '../../services/password.service';

@Component({
  selector: 'app-set-password',
  templateUrl: './set-password.component.html',
  styleUrls: ['./set-password.component.scss'],
})
export class SetPasswordComponent extends BasePageComponent implements OnInit {
  formGroup!: FormGroup;
  mode: PageMode = 'LOADING';

  // settings
  subtitle: string = '';
  languages: any = environment.languageCodes;
  defaultLanguage: string = 'en';
  languageCodes = environment.languageCodes;

  setPasswordToken!: string;
  data: any = [];

  // custom translation strings for specific form control errors
  errorTranslations = {
    password: [{ invalidpassword: 'errors.field.invalid_password' }],
    confirmPassword: [
      {
        notmatch: 'errors.field.password_confirm_not_match',
      },
    ],
  };

  // min length for `password` form control
  minPasswordLength = environment.userPasswordMinLength;

  // max length for `password` form control
  maxPasswordLength = environment.userPasswordMaxLength;

  constructor(
    private fb: FormBuilder,
    private passwordService: PasswordService,
    private alerts: AlertsService,
    private activatedRoute: ActivatedRoute,
    private router: Router,
    private settingService: SettingService
  ) {
    super();
  }

  ngOnInit(): void {
    this.fetchSetting();
    this.activatedRoute.params.pipe(takeUntil(this.onDestroy$)).subscribe(params => {
      if (params) {
        this.setPasswordToken = params['token'];
        this.validateToken();
      } else {
        // redirects when no set-password id was set
        this.router.navigate(['/']);
      }
    });
    this.initFormGroup();
  }

  /**
   * Setup form controls and initial validators
   */
  private initFormGroup() {
    this.formGroup = this.fb.group(
      {
        email: [{ value: '', disabled: true }, [Validators.required, Validators.email]],
        password: [
          '',
          [
            Validators.required,
            Validators.minLength(this.minPasswordLength),
            Validators.maxLength(this.maxPasswordLength),
            CustomValidators.passwordFormat,
          ],
        ],
        confirmPassword: [
          '',
          [
            Validators.required,
            Validators.minLength(this.minPasswordLength),
            Validators.maxLength(this.maxPasswordLength),
          ],
        ],
      },
      {
        validators: [CustomValidators.fieldConfirm('password', 'confirmPassword')],
      }
    );
  }

  private validateToken() {
    this.passwordService.isSetUserPasswordTokenValid(this.setPasswordToken).subscribe({
      next: valid => {
        this.mode = valid ? 'READY' : 'INVALID';
        this.data = valid;
        this.formGroup.controls['email'].setValue(this.data.data.email);
      },
      error: err => {
        this.mode = err ? 'INVALID' : 'LOADING';
      },
    });
  }

  /**
   * Fetch setting data
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
   * Submit reset password
   */
  onSubmit() {
    const email = this.formGroup.controls['email'].value;
    const password = this.formGroup.controls['password'].value;

    if (this.formGroup.valid) {
      this.passwordService.setUserPassword(this.setPasswordToken, email, password).subscribe(res => {
        if (res) {
          this.mode = 'FINISHED';
        } else {
          this.alerts.setError('errors.something_went_wrong');
        }
      });
    }
  }
}

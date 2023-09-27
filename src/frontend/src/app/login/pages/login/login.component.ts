import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { SettingService } from 'src/app/administration/services/settings/setting.service';
import { PageMode } from 'src/app/shared/interfaces/common.interface';
import { AlertsService } from 'src/app/shared/services/alerts/alerts.service';
import { LanguageService } from 'src/app/shared/services/language/language.service';
import { environment } from 'src/environments/environment';
import { AuthService } from '../../services/auth/auth.service';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.scss'],
})
export class LoginComponent implements OnInit {
  mode: PageMode = 'READY';
  name?: string;
  returnUrl = '/';
  loginForm!: FormGroup;
  passwordVisible = false;

  // settings
  subtitle: string = '';
  languages: any = environment.languageCodes;
  defaultLanguage: string = 'en';
  mfaAuthentication: boolean = false;

  languageCodes = environment.languageCodes;

  constructor(
    private fb: FormBuilder,
    private router: Router,
    private activatedRoute: ActivatedRoute,
    public languageService: LanguageService,
    private alerts: AlertsService,
    private auth: AuthService,
    private settingService: SettingService
  ) {}

  ngOnInit(): void {
    this.fetchUser();
    this.fetchSetting();

    this.defaultLanguage = this.settingService.currentLanguage
      ? this.settingService.currentLanguage.defaultLanguage ?? 'en'
      : 'en';

    // Setup form controls and initial validators
    this.loginForm = this.fb.group({
      email: ['', [Validators.required, Validators.email]],
      password: ['', Validators.required],
    });

    if (this.mfaAuthentication) {
      this.returnUrl = '/login/two-factor';
      if (this.returnUrl !== '/login/two-factor' && this.returnUrl.includes('?')) {
        this.returnUrl = this.returnUrl.split('?')[0];
      }
    } else {
      this.returnUrl = this.activatedRoute.snapshot.queryParams['returnUrl'] || '/';
      if (this.returnUrl !== '/' && this.returnUrl.includes('?')) {
        this.returnUrl = this.returnUrl.split('?')[0];
      }
    }
  }

  // Show the password
  togglePasswordVisibility() {
    this.passwordVisible = !this.passwordVisible;
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
            this.mfaAuthentication = res.data.securitySetting
              ? res.data.securitySetting.mfaAuthentication ?? false
              : false;

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
   * Fetch currently signed in user data
   */
  private fetchUser() {
    if (this.auth.currentUser == null)
      this.auth.fetchCurrentUser.subscribe({
        next: res => {
          if (res) {
            this.name = res.name;
            this.mode = 'FINISHED';
          }
        },
      });
  }

  /**
   * Log in attempt
   */
  login() {
    if (this.loginForm.valid) {
      const { email, password } = this.loginForm.value;
      this.auth.login(email.trim(), password.trim()).subscribe(res => {
        if (res) {
          this.router.navigate([this.returnUrl]);
        } else {
          if (this.auth.maxMinutes !== null) {
            const data = this.auth.maxMinutes;
            this.alerts.setErrorWithData('auth.msg.error_max_failed_login', data);
          } else {
            this.alerts.setError('auth.msg.error_sign_in');
          }
        }
      });
    }
  }
}

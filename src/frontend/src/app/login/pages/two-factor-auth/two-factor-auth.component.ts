import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { SettingService } from 'src/app/administration/services/settings/setting.service';
import { AlertsService } from 'src/app/shared/services/alerts/alerts.service';
import { AuthService } from '../../services/auth/auth.service';

@Component({
  selector: 'app-two-factor-auth',
  templateUrl: './two-factor-auth.component.html',
  styleUrls: ['./two-factor-auth.component.scss'],
})
export class TwoFactorAuthComponent implements OnInit {
  returnUrl = '/';
  formGroup!: FormGroup;
  email: string | undefined;
  delay!: number;
  resendButton: boolean = false;
  timer: string | null = null;

  constructor(
    private fb: FormBuilder,
    public authService: AuthService,
    private router: Router,
    private activatedRoute: ActivatedRoute,
    private alerts: AlertsService,
    private settingService: SettingService
  ) {
    this.initFormGroup();
  }

  ngOnInit(): void {
    const email = this.authService.currentTwoFactor?.userEmail;
    this.email = email?.replace(/(\w{3})[\w.-]+@([\w.]+\w)/, '$1****@********');

    this.setDelayResendToken();

    this.returnUrl = this.activatedRoute.snapshot.queryParams['returnUrl'] || '/';

    if (this.returnUrl !== '/' && this.returnUrl.includes('?')) {
      this.returnUrl = this.returnUrl.split('?')[0];
    }
  }

  /**
   * Event handler for `paste verification code` to form
   * @param event ClipboardEvent
   */
  paste(event: ClipboardEvent) {
    let clipboardText = event.clipboardData?.getData('text');

    const data: any = clipboardText?.replace(/\s/g, '');

    this.formGroup.setValue({
      form1: data[0],
      form2: data[1],
      form3: data[2],
      form4: data[3],
      form5: data[4],
      form6: data[5],
    });
  }

  /**
   * Show time delay for sending the token authenticator
   */
  private setDelayResendToken() {
    this.settingService.getSettingsAll().subscribe(res => {
      if (res && res.success) {
        this.delay = res.data.securitySetting ? res.data.securitySetting.mfaDelayResend : 0;
        let minute = this.delay;
        let seconds: number = minute * 60;
        let textSec: any = '0';
        let startSec: number = 60;

        const prefix = minute < 10 ? '0' : '';

        const timer = setInterval(() => {
          seconds--;
          if (startSec != 0) startSec--;
          else startSec = 59;

          if (startSec < 10) {
            textSec = '0' + startSec;
          } else textSec = startSec;

          this.timer = `${prefix}${Math.floor(seconds / 60)}:${textSec}`;

          if (seconds == 0) {
            this.resendButton = true;
            clearInterval(timer);
          }
        }, 1000);
      }
    });
  }

  /**
   * Setup form controls and initial validators
   */
  private initFormGroup() {
    this.formGroup = this.fb.group({
      form1: new FormControl('', [Validators.required, Validators.maxLength(1)]),
      form2: new FormControl('', [Validators.required, Validators.maxLength(1)]),
      form3: new FormControl('', [Validators.required, Validators.maxLength(1)]),
      form4: new FormControl('', [Validators.required, Validators.maxLength(1)]),
      form5: new FormControl('', [Validators.required, Validators.maxLength(1)]),
      form6: new FormControl('', [Validators.required, Validators.maxLength(1)]),
    });
  }

  /**
   * Set focus form controls
   *
   * @param {any} event
   * @param {number} step
   * @returns
   */
  onDigitInput(event: any, step: number) {
    const prevElement = document.getElementById('form' + (step - 1));
    const nextElement = document.getElementById('form' + (step + 1));

    if (event.code == 'Backspace' && event.target.value === '') {
      event.target.parentElement.parentElement.children[step - 6 > 0 ? step - 6 : 0].children[0].value = '';

      if (prevElement) {
        prevElement.focus();
        return;
      }
    } else {
      if (nextElement) {
        nextElement.focus();
        return;
      } else {
      }
    }
  }

  /**
   * Submit token Two-Factor Google Authenticator or email
   */
  submit() {
    if (this.authService.currentTwoFactor == null) {
      return this.alerts.setError(`auth.msg.error_token_verify`);
    }

    const token1 = this.formGroup.controls['form1'].value;
    const token2 = this.formGroup.controls['form2'].value;
    const token3 = this.formGroup.controls['form3'].value;
    const token4 = this.formGroup.controls['form4'].value;
    const token5 = this.formGroup.controls['form5'].value;
    const token6 = this.formGroup.controls['form6'].value;

    const otpToken = token1 + token2 + token3 + token4 + token5 + token6;
    const email = this.authService.currentTwoFactor.userEmail;

    if (this.formGroup.valid) {
      this.authService.authenticateMfaToken(email, otpToken).subscribe(res => {
        if (res) {
          this.router.navigate([this.returnUrl]);
        } else {
          if (this.authService.maxMinutes !== null) {
            const data = this.authService.maxMinutes;
            this.router.navigate(['/login']);
            this.alerts.setErrorWithData('auth.msg.error_max_failed_two_factor', data);
          } else {
            this.alerts.setError(`auth.msg.error_token_verify`);
          }
        }
      });
    }
  }

  /**
   * Send to email and get token Two-Factor Authenticator
   */
  resendToken() {
    if (this.authService.currentTwoFactor == null) return;

    this.authService.resendMfaToken(this.authService.currentTwoFactor.userEmail).subscribe(res => {
      if (res) {
        this.setDelayResendToken();
        this.resendButton = false;
        this.alerts.setSuccess('auth.msg.token_send');
      } else {
        const data = this.authService.maxMinutes ? this.authService.maxMinutes : '';
        this.router.navigate(['/login']);
        this.alerts.setErrorWithData('auth.msg.error_max_failed_two_factor', data);
      }
    });
  }
}

import { Component, Input, OnDestroy, OnInit } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { takeUntil } from 'rxjs';
import { SettingService } from 'src/app/administration/services/settings/setting.service';
import { MailSetting } from 'src/app/apps/interfaces/app.interface';
import { BaseComponent } from 'src/app/shared/component/bases/base.component';
import { FormDropdown } from 'src/app/shared/interfaces/common.interface';
import { MailSettingService } from 'src/app/shared/services/mail-setting/mail-setting.service';

@Component({
  selector: 'app-mail-setting',
  templateUrl: './mail-setting.component.html',
  styleUrls: ['./mail-setting.component.scss'],
})
export class MailSettingComponent extends BaseComponent implements OnInit, OnDestroy {
  // if set, this will be used to the set sub title for email setting
  @Input() subtitle!: string;

  // if set, this will be used to fetch E-mail data model for this page (and `edit` will be true)
  @Input() id!: string;

  mailSetting!: MailSetting;
  formGroup!: FormGroup;

  encryption: FormDropdown[] = [
    { value: 'none', description: 'None' },
    { value: 'ssl', description: 'SSL' },
    { value: 'tls', description: 'TLS' },
  ];

  constructor(
    private fb: FormBuilder,
    private settingService: SettingService,
    public mailSettingService: MailSettingService
  ) {
    super();

    this.initFormGroup();
  }

  override ngOnDestroy(): void {
    this.mailSettingService.ngOnDestroy();
  }

  ngOnInit(): void {
    this.settingService.selectedIndex = 0;

    this.initMailSetting();
  }

  /**
   * Get email data for global setting email
   */
  private initMailSetting() {
    this.settingService
      .listenOnSmtpSetting()
      .pipe(takeUntil(this.onDestroy$))
      .subscribe(mail => {
        if (mail != null) {
          this.mailSetting = mail;

          this.formGroup.controls['changePassword'].reset();
          this.formGroup.controls['password'].reset();
          this.initSetValue(this.mailSetting);
        } else {
          this.formGroup.controls['encryption'].setValue(this.encryption[0].value);
          this.onChangePasswordChanged(true);
        }
      });
  }

  /**
   * Set value form
   *
   * @param {MailSetting} data
   */
  private initSetValue(data: MailSetting) {
    this.formGroup.patchValue({
      host: data.smtpHost,
      port: data.smtpPort,
      username: data.username,
      password: '',
      encryption: data.encryption,
      email: data.fromEmail,
      name: data.fromName,
    });
  }

  /**
   * Setup form controls and initial validators
   *
   * @private
   */
  private initFormGroup() {
    this.formGroup = this.fb.group({
      host: new FormControl('', [Validators.required, Validators.maxLength(255)]),
      port: new FormControl('', [Validators.required, Validators.maxLength(3)]),
      username: new FormControl('', [Validators.required]),
      changePassword: new FormControl(''),
      password: new FormControl('', []),
      encryption: new FormControl(''),
      email: new FormControl('', [Validators.required, Validators.email, Validators.maxLength(255)]),
      name: new FormControl('', [Validators.required, Validators.maxLength(255)]),
    });
  }

  /**
   * Event handler for `change password` toggle
   * @param toChange enabled or disabled state
   */
  onChangePasswordChanged(toChange: boolean) {
    const passwordControl = this.formGroup.controls['password'];

    if (toChange) {
      passwordControl.setValidators([Validators.required]);
    } else {
      passwordControl.setValidators(null);
      passwordControl.setValue(null);
    }

    this.formGroup.updateValueAndValidity();
  }

  /**
   * Build request payload for saving Email setting global or app setting data
   *
   * @returns {MailSetting} MailSetting
   * @private
   */
  private buildNotifEmailRequest(): MailSetting {
    return {
      smtpHost: this.formGroup.controls['host'].value,
      smtpPort: this.formGroup.controls['port'].value,
      username: this.formGroup.controls['username'].value,
      password: this.formGroup.controls['password'].value,
      encryption: this.formGroup.controls['encryption'].value,
      fromEmail: this.formGroup.controls['email'].value,
      fromName: this.formGroup.controls['name'].value,
    };
  }

  /**
   * Test mail connection for global or app setting
   */
  checkMailConnection() {
    if (this.formGroup.valid) {
      this.mailSettingService.openCheckDialog({
        mailSetting: this.buildNotifEmailRequest(),
        appId: this.id,
      });
    }
  }
}

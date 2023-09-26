import { Component, Inject } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { marker as _ } from '@biesbjerg/ngx-translate-extract-marker';
import { AlertsService } from '../../services/alerts/alerts.service';
import { MailSettingService } from '../../services/mail-setting/mail-setting.service';

@Component({
  selector: 'app-mail-connection-dialog',
  templateUrl: './mail-connection-dialog.component.html',
  styleUrls: ['./mail-connection-dialog.component.scss'],
})
export class MailConnectionDialogComponent {
  checkForm!: FormGroup;
  validateForm!: FormGroup;
  actions = this.mailSettingService.types;
  type: string;
  btnTitle: string;
  recipientEmail!: string;

  constructor(
    private fb: FormBuilder,
    public mailSettingService: MailSettingService,
    private alert: AlertsService,
    private dialofRef: MatDialogRef<MailConnectionDialogComponent>,
    @Inject(MAT_DIALOG_DATA) data: any
  ) {
    this.type = data.type;
    this.recipientEmail = data.email;

    this.btnTitle = this.type === mailSettingService.types.check ? 'mail.button_send' : 'mail.button_validation';
    this.formInit();
  }

  /**
   * Setup form controls and initial validators
   */
  private formInit() {
    this.checkForm = this.fb.group({
      email: new FormControl('', [Validators.required, Validators.email]),
    });

    this.validateForm = this.fb.group({
      digit1: new FormControl('', [Validators.required, Validators.minLength(1)]),
      digit2: new FormControl('', [Validators.required, Validators.minLength(1)]),
      digit3: new FormControl('', [Validators.required, Validators.minLength(1)]),
      digit4: new FormControl('', [Validators.required, Validators.minLength(1)]),
      digit5: new FormControl('', [Validators.required, Validators.minLength(1)]),
      digit6: new FormControl('', [Validators.required, Validators.minLength(1)]),
    });
  }

  /**
   * Next or previous form focus
   *
   * @param event
   * @param step
   */
  inputKeyUp(event: any, step: number) {
    const prevElement = document.getElementById('digit' + (step - 1));
    const nextElement = document.getElementById('digit' + (step + 1));

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
   * Paste token from clipboard
   *
   * @param event
   */
  paste(event: ClipboardEvent) {
    let clipboardText = event.clipboardData?.getData('text');
    const data: any = clipboardText?.replace(/\s/g, '');

    this.validateForm.setValue({
      digit1: data[0],
      digit2: data[1],
      digit3: data[2],
      digit4: data[3],
      digit5: data[4],
      digit6: data[5],
    });
  }

  /**
   * Combine input token
   */
  private combineToken(): string {
    const digit1 = this.validateForm.controls['digit1'].value;
    const digit2 = this.validateForm.controls['digit2'].value;
    const digit3 = this.validateForm.controls['digit3'].value;
    const digit4 = this.validateForm.controls['digit4'].value;
    const digit5 = this.validateForm.controls['digit5'].value;
    const digit6 = this.validateForm.controls['digit6'].value;

    return digit1 + digit2 + digit3 + digit4 + digit5 + digit6;
  }

  /**
   * Send mail to recipient to test the connection
   */
  send() {
    if (!this.checkForm.invalid || !this.validateForm.invalid) {
      if (this.type === this.mailSettingService.types.check) {
        this.dialofRef.close();
        this.alert.setSuccess(_('mail.msg.send_token_to_recipient'));
        this.mailSettingService.checkConnection(this.checkForm.controls['email'].value);
      }

      if (this.type === this.mailSettingService.types.validate) {
        this.mailSettingService.validateConnection(this.combineToken());
      }
    }
  }

  /**
   * Close modal dialog
   */
  close() {
    this.dialofRef.close(true);
  }
}

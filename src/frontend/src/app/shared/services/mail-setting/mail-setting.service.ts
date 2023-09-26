import { Injectable, OnDestroy } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { Router } from '@angular/router';
import { marker as _ } from '@biesbjerg/ngx-translate-extract-marker';
import { Subject, takeUntil } from 'rxjs';
import { SettingService } from 'src/app/administration/services/settings/setting.service';
import { MailSetting } from 'src/app/apps/interfaces/app.interface';
import { ConfirmDialogComponent } from '../../component/confirm-dialog/confirm-dialog.component';
import { MailConnectionDialogComponent } from '../../component/mail-connection-dialog/mail-connection-dialog.component';
import { CheckMailConnection } from '../../interfaces/common.interface';
import { AlertsService } from '../alerts/alerts.service';

@Injectable({
  providedIn: 'root',
})
export class MailSettingService implements OnDestroy {
  private testId!: string;
  private recipientEmail!: string;
  protected onDestroy$ = new Subject<void>();

  public mailConnection!: CheckMailConnection;
  public types = { check: 'CHECK', validate: 'VALIDATE' };

  constructor(
    private settingService: SettingService,
    private alert: AlertsService,
    private dialog: MatDialog,
    private router: Router
  ) {}

  ngOnDestroy(): void {
    this.onDestroy$.next();
    this.onDestroy$.complete();
  }

  /**
   * Open test connection dialog
   */
  openCheckDialog(mail: CheckMailConnection) {
    this.mailConnection = mail;
    this.dialog.open(MailConnectionDialogComponent, {
      data: { mail: this.mailConnection.mailSetting, type: this.types.check },
      minWidth: '420px',
      maxWidth: '600px',
      disableClose: true,
    });
  }

  /**
   * Open validation dialog
   */
  private openValidationDialog(): void {
    this.dialog
      .open(MailConnectionDialogComponent, {
        data: {
          type: this.types.validate,
          email: this.recipientEmail,
        },
        minWidth: '420px',
        disableClose: true,
      })
      .afterClosed()
      .subscribe({
        next: res => {
          if (res) {
            this.dialog
              .open(ConfirmDialogComponent, {
                data: {
                  title: 'common.confirmation',
                  message: 'mail.msg.close_validation_dialog',
                },
                maxWidth: '400px',
              })
              .afterClosed()
              .subscribe({
                next: res => {
                  if (res) {
                    this.dialog.closeAll();
                  } else {
                    this.openValidationDialog();
                  }
                },
                error: err => {},
              });
          }
        },
        error: err => {},
      });
  }

  /**
   * Checks the mail connection.
   *
   * @param recipient
   */
  checkConnection(recipient: string) {
    this.recipientEmail = recipient;

    const connection = this.settingService.testMailConnection(this.cMailSettingWEmail(this.recipientEmail));

    connection.pipe(takeUntil(this.onDestroy$)).subscribe({
      next: res => {
        if (res.success) {
          this.testId = res.data.id;
          this.openValidationDialog();
        } else {
          this.alert.setError(_('mail.msg.error_smtp_connection'));
        }
      },
      error: err => {
        this.alert.setError(_('mail.msg.error_smtp_connection'));
      },
    });
  }

  /**
   * Validates a mail connection using the provided token.
   *
   * @param token
   */
  validateConnection(token: string) {
    const connection = this.settingService.validateMailConnection(this.testId, this.cMailSettingWToken(token));

    connection.pipe(takeUntil(this.onDestroy$)).subscribe({
      next: res => {
        if (res.success) {
          this.dialog.closeAll();

          this.router.navigate([`/administration/settings`]);
          this.settingService.getMailSetting();
          this.alert.setSuccess(_('mail.msg.connection_is_valid'));
        } else {
          this.alert.setError(_('mail.msg.connection_not_valid'));
        }
      },
      error: err => {
        this.alert.setError(_('mail.msg.connection_not_valid'));
      },
    });
  }

  /**
   * Create mail seting payload with email
   *
   * @param recipient
   */
  private cMailSettingWEmail(recipient: string): MailSetting {
    let mail: MailSetting = this.createMailSetting();
    mail.testEmail = recipient;

    return mail;
  }

  /**
   * Create mail setting payload with token
   *
   * @param token
   */
  private cMailSettingWToken(token: string): MailSetting {
    let mail: MailSetting = this.createMailSetting();
    mail.token = token;

    return mail;
  }

  private createMailSetting(): MailSetting {
    return {
      smtpHost: this.mailConnection.mailSetting.smtpHost,
      smtpPort: this.mailConnection.mailSetting.smtpPort,
      username: this.mailConnection.mailSetting.username,
      password: this.mailConnection.mailSetting.password,
      encryption: this.mailConnection.mailSetting.encryption,
      fromEmail: this.mailConnection.mailSetting.fromEmail,
      fromName: this.mailConnection.mailSetting.fromName,
    };
  }
}

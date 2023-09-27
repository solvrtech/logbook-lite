import { Component, Input } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { Router } from '@angular/router';
import { marker as _ } from '@biesbjerg/ngx-translate-extract-marker';
import { AppService } from 'src/app/apps/services/apps/app.service';
import { MessageService } from 'src/app/messages/services/message/message.service';
import { ConfirmDialogComponent } from 'src/app/shared/component/confirm-dialog/confirm-dialog.component';
import { AlertsService } from 'src/app/shared/services/alerts/alerts.service';

@Component({
  selector: 'app-delete-app',
  templateUrl: './delete-app.component.html',
  styleUrls: ['./delete-app.component.scss'],
})
export class DeleteAppComponent {
  // if set, this will be used to fetch the App data model for this page (and `App setting delete` will be true)
  @Input() id!: string;

  constructor(
    private dialog: MatDialog,
    private appService: AppService,
    private alert: AlertsService,
    private router: Router,
    private messageService: MessageService
  ) {}

  /**
   * Show dialog confirmation for deleting App
   */
  onDeleteApp() {
    this.dialog
      .open(ConfirmDialogComponent, {
        data: {
          title: 'common.confirmation',
          message: 'common.msg.delete_app',
          inputName: true,
        },
      })
      .afterClosed()
      .subscribe(res => {
        if (res) {
          this.appService.deleteApp(this.id, res.name).subscribe({
            next: res => {
              if (res && res.success) {
                this.messageService.refreshMessage();
                this.alert.setSuccessWithData('app.msg.success.delete_app', res.data.name);
                this.router.navigate(['/main-menu/apps']);
              } else {
                this.alert.setError(_('common.msg.something_went_wrong'));
              }
            },
            error: err => {
              this.alert.setError(_('common.msg.something_went_wrong'));
            },
          });
        }
      });
  }
}

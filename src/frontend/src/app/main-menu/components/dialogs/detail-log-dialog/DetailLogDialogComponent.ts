import { Component, Inject } from '@angular/core';
import { MAT_DIALOG_DATA, MatDialog, MatDialogRef } from '@angular/material/dialog';
import { SettingService } from 'src/app/administration/services/settings/setting.service';
import { AppService } from 'src/app/apps/services/apps/app.service';
import { AuthService } from 'src/app/login/services/auth/auth.service';
import { LogService } from 'src/app/logs/services/logs/log.service';
import { AppSharedService } from 'src/app/main-menu/services/app-shared/app-shared.service';
import { App, AppLogo } from '../../../../apps/interfaces/app.interface';
import { DetailLogComponent } from '../../detail-log/detail-log.component';
export interface DetailLog {
  appId: string;
  logId: string;
}

@Component({
  selector: 'app-detail-log-dialog',
  templateUrl: './detail-log-dialog.component.html',
  styleUrls: ['./detail-log-dialog.component.scss'],
})
export class DetailLogDialogComponent extends DetailLogComponent {
  app!: App;
  appLogo!: AppLogo;
  appId!: string;
  logId!: string;
  permission: boolean = true;

  constructor(
    logService: LogService,
    authService: AuthService,
    dialog: MatDialog,
    settingService: SettingService,
    appSharedService: AppSharedService,

    private appService: AppService,
    public dialogRef: MatDialogRef<DetailLogDialogComponent>,
    @Inject(MAT_DIALOG_DATA) data: DetailLog
  ) {
    super(logService, authService, dialog, settingService, appSharedService);

    this.appId = data.appId;
    this.logId = data.logId;
    if (this.appId && this.logId) {
      this.appLogDetail(this.appId, this.logId);
    } else {
      this.logDetail(this.logId);
    }
  }

  private logDetail(id: string) {
    this.appService.getLogDetail(id).subscribe({
      next: res => {
        if (res && res.success) {
          this.log = res.data.log;
          this.permission = !!this.log.isTeamManager ?? false;
          this.app = this.log.app;

          this.comments = res.data.comments;
        }
      },
      error: err => {},
    });
  }

  /**
   * Fetch app log data
   *
   * @param {number} appId : app id
   * @param {number} logId : log id
   */
  private appLogDetail(appId: string, logId: string) {
    this.appService.getAppLogDetail(appId, logId).subscribe({
      next: res => {
        if (res) {
          this.appService.selected = 1;
          this.log = res.log;
          this.permission = !!this.log.isTeamManager ?? false;

          this.app = this.log.app;
          this.comments = res.comments;
        }
      },
      error: err => {},
    });
  }

  /**
   * Show the form for editing comment
   *
   * @param {number} i
   */
  onEditComment(i: number) {
    this.editMode = true;
    this.number = i;
  }

  onDismiss() {
    this.dialogRef.close(true);
  }
}

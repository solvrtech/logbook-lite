import { Component, Input } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { Params } from '@angular/router';
import { takeUntil } from 'rxjs';
import { SettingService } from 'src/app/administration/services/settings/setting.service';
import { AuthService } from 'src/app/login/services/auth/auth.service';
import { BaseComponent } from 'src/app/shared/component/bases/base.component';
import { ConfirmDialogComponent } from 'src/app/shared/component/confirm-dialog/confirm-dialog.component';
import { formatDateTime } from 'src/app/shared/helpers/date.helper';
import { BreadCrumb } from 'src/app/shared/interfaces/common.interface';
import { Comment, Log } from '../../../logs/interfaces/log.interface';
import { LogService } from '../../../logs/services/logs/log.service';
import { AppSharedService } from '../../services/app-shared/app-shared.service';

@Component({
  selector: 'app-detail-log',
  templateUrl: './detail-log.component.html',
  styleUrls: ['./detail-log.component.scss'],
})
export class DetailLogComponent extends BaseComponent {
  @Input() hasTitle!: boolean;
  // if set, this will be used to set the URL target for the back button
  @Input() queryParams?: Params;

  // if set, this will be used to set the URL target for the back button
  @Input() returnUrl?: string;

  // if set, this will be used to set the current page state
  @Input() state: any;

  // if set, this will override the default page's breadcrumb
  @Input() breadCrumbs!: BreadCrumb[];

  // if set, this will be used to the Log data model for this page (and `show` will be true)
  @Input() log!: Log;

  // if set, this will be used to the Log date for this page
  @Input() date!: string;

  // if set, this will be used to set error message
  @Input() message!: string;

  // if set, this will be used to the comments data model for this page (and `show` will be true)
  @Input() comments!: Comment[];

  editMode: boolean = false;
  number: number | null = null;
  isLoading: boolean = false;
  isLoadingEdit: boolean = false;

  constructor(
    private logService: LogService,
    public authService: AuthService,
    private dialog: MatDialog,
    private settingService: SettingService,
    private appSharedService: AppSharedService
  ) {
    super();
  }

  /**
   * Get log level for the setting css badge
   *
   * @returns
   */
  badgeLogLevel(level: string) {
    return this.appSharedService.getBagdeAppLogLevel(level);
  }

  /**
   * Get log stack trace
   *
   * @returns
   */
  get stackTraceLog() {
    let stackTrace = this.log.stackTrace
      .map((st: any) => st.replace(/\s/g, ''))
      .join(',')
      .replace(/,/g, '\n');
    return stackTrace;
  }

  /**
   * Get log additional
   *
   * @returns
   */
  get additional() {
    let additional = this.log.additional
      .map((add: any) => add.replace(/\s/g, ''))
      .join(',')
      .replace(/,/g, '\n');
    return additional;
  }

  private fetchComment(id: string) {
    this.logService.getComment(id);
    this.logService.comments.pipe(takeUntil(this.onDestroy$)).subscribe(res => {
      this.comments = res;
    });
  }

  /**
   * Saved comment data
   *
   * @param {any} event
   */
  onChange(event: any) {
    const comment = event.target.value;
    this.isLoading = true;

    this.logService.logComment(this.log.id, comment).subscribe({
      next: res => {
        if (res && res.success) {
          setTimeout(() => {
            this.fetchComment(this.log.id);
            event.target.value = '';
            this.isLoading = false;
          }, 1500);
        }
      },
    });
  }

  /**
   * Show the form for editing comment
   *
   * @param {number} i
   */
  edit(i: number) {
    this.editMode = true;
    this.number = i;
  }

  /**
   * Hide the form for editing comment
   */
  close() {
    this.editMode = false;
  }

  /**
   * Saved and updated comment data
   *
   * @param {any} event
   * @param {number} id
   */
  onChangeEdit(event: any, id: number) {
    const comment = event.target.value;
    this.isLoadingEdit = true;

    this.logService.editComment(this.log.id, id, comment).subscribe({
      next: res => {
        if (res && res.success) {
          setTimeout(() => {
            this.fetchComment(this.log.id);
            event.target.value = '';

            this.isLoadingEdit = false;
            this.editMode = false;
          }, 1500);
        }
      },
    });
  }

  /**
   * Show confirmation for delete comment data
   *
   * @param {number} id
   */
  deleteComment(id: number) {
    this.dialog
      .open(ConfirmDialogComponent, {
        data: {
          title: 'common.confirmation',
          message: 'common.msg.delete',
        },
      })
      .afterClosed()
      .subscribe(res => {
        if (res) {
          this.logService.deleteComment(this.log.id, id).subscribe({
            next: res => {
              if (res && res.success) {
                this.fetchComment(this.log.id);
              }
            },
          });
        }
      });
  }

  /**
   * Set default format date comment
   *
   * @param {string} data
   * @returns
   */
  dateTime(data: string) {
    return formatDateTime({ date: data }, this.settingService);
  }

  appIcon(type: string) {
    return this.appSharedService.getAppIcon(type);
  }
}

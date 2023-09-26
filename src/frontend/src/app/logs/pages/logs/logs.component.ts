import { Component, OnInit } from '@angular/core';
import { FormControl } from '@angular/forms';
import { MatDialog } from '@angular/material/dialog';
import { marker as _ } from '@biesbjerg/ngx-translate-extract-marker';
import { BehaviorSubject, ReplaySubject } from 'rxjs';
import { takeUntil } from 'rxjs/operators';
import { RoleService } from 'src/app/administration/services/role.service';
import { SettingService } from 'src/app/administration/services/settings/setting.service';
import { UserService } from 'src/app/administration/services/user.service';
import { AppService } from 'src/app/apps/services/apps/app.service';
import { DetailLogDialogComponent } from 'src/app/main-menu/components/dialogs/detail-log-dialog/DetailLogDialogComponent';
import { SEARCH_DROPDOWN_SEVERITY, SEARCH_DROPDOWN_STATUS_CONFIG } from 'src/app/main-menu/data/dropdown-config.data';
import { IGNORED, ON_REVIEW, RESOLVED } from 'src/app/main-menu/data/status.data';
import { AppSharedService } from 'src/app/main-menu/services/app-shared/app-shared.service';
import { BaseSecurePageComponent } from 'src/app/shared/component/bases/base-secure-page.component';
import { dateFrom, dateTo, formatDate, formatDateTime } from 'src/app/shared/helpers/date.helper';
import { SearchFieldOptionValue, SearchFields } from 'src/app/shared/interfaces/search.interface';
import { TableColumns, TableRowMenuItems } from 'src/app/shared/interfaces/table.interface';
import { AppPage, AppRole } from 'src/app/starter/data/permissions.data';
import { LogsDataSource } from '../../datasources/logs.datasource';
import { Log } from '../../interfaces/log.interface';
import { LogService } from '../../services/logs/log.service';

@Component({
  selector: 'app-logs',
  templateUrl: './logs.component.html',
  styleUrls: ['./logs.component.scss'],
})
export class LogsComponent extends BaseSecurePageComponent implements OnInit {
  dataSource!: LogsDataSource;
  columns$ = new BehaviorSubject<TableColumns>([]);
  rowMenus$ = new BehaviorSubject<TableRowMenuItems>([]);
  searchFields$ = new BehaviorSubject<SearchFields>([]);
  severity$ = new BehaviorSubject<SearchFieldOptionValue[]>([]);
  status$ = new BehaviorSubject<SearchFieldOptionValue[]>([]);
  app$ = new BehaviorSubject<SearchFieldOptionValue[]>([]);
  user$ = new BehaviorSubject<SearchFieldOptionValue[]>([]);
  filteredApp$ = new ReplaySubject<SearchFieldOptionValue[]>(1);
  filteredUser$ = new ReplaySubject<SearchFieldOptionValue[]>(1);

  // Get name page
  override pageName: string = AppPage.APP;

  formApp = new FormControl();
  formUser = new FormControl();
  startDateTime!: Date;
  endDateTime!: Date;

  // Search dropdown
  severity: SearchFieldOptionValue[] = SEARCH_DROPDOWN_SEVERITY;
  status: SearchFieldOptionValue[] = SEARCH_DROPDOWN_STATUS_CONFIG;

  /**
   * Get permission to view logs page
   *
   * @return {string[]}
   */
  get pagePermissions(): string[] {
    return [AppRole.ROLE_ADMIN, AppRole.ROLE_STANDARD];
  }

  constructor(
    protected override roleService: RoleService,
    private logService: LogService,
    private userService: UserService,
    private settingService: SettingService,
    private dialog: MatDialog,
    private appSharedService: AppSharedService,
    private appService: AppService
  ) {
    super(roleService);

    // data source creation is suggested to be done here
    this.dataSource = new LogsDataSource(this.logService);
  }

  onInit(): void {
    this.setTableColumns();
    this.setSearchFields();
    this.setTableRowMenus();
    this.fetchApps();
    this.fetchUsers();
    this.severity$.next(this.severity);
    this.status$.next(this.status);

    // mandatory or the page will be blank:
    this.pageState.state = 'loaded';
  }

  override ngOnDestroy(): void {
    super.ngOnDestroy();
  }

  /**
   * Set the log's data table columns
   *
   * @private
   */
  private setTableColumns() {
    this.columns$.next([
      {
        name: 'title.app',
        key: 'app',
        logo: (data: Log) => data.appLogo,
        icon: (data: Log) => this.appSharedService.getAppIcon(data.appType),
      },
      {
        name: 'common.datetime',
        key: 'dateTime',
        getValue: (data: Log) => formatDateTime({ date: data.dateTime }, this.settingService),
      },
      {
        name: 'common.severity',
        key: 'level',
        badge: (data: Log) => this.appSharedService.getBagdeAppLogLevel(data.level),
      },
      {
        name: 'common.message',
        key: 'message',
        getValue: (data: Log) => {
          let val = data.message.toString();
          return val.length >= 50 ? `${val.substring(0, 50)}...` : val.substring(0, 50);
        },
      },
      {
        name: 'common.status',
        key: 'status',
        getValue: (data: Log) =>
          data.status == IGNORED
            ? 'common.ignored'
            : data.status == ON_REVIEW
            ? 'common.on_review'
            : data.status == RESOLVED
            ? 'common.resolved'
            : 'common.new',
        badge: (data: Log) =>
          data.status == IGNORED
            ? 'badge rounded-pill text-danger'
            : data.status == ON_REVIEW
            ? 'badge rounded-pill text-bg-orange'
            : data.status == RESOLVED
            ? 'badge rounded-pill text-success'
            : 'badge rounded-pill text-bg-primary',
      },
      {
        name: 'common.assignee',
        key: 'assignee',
      },
    ]);
  }

  /**
   * Build dropdown search data for `apps` field
   *
   * @private
   */
  private fetchApps() {
    this.appService.getAppSearch().subscribe({
      next: res => {
        if (res != null) {
          const apps: SearchFieldOptionValue[] = [];
          res.data.forEach((app: any) =>
            apps.push({
              value: app.id,
              label: app.name,
            })
          );
          this.app$.next(apps);
        }
      },
    });
  }

  /**
   * Build dropdown search data for `assignee` field
   *
   * @private
   */
  private fetchUsers() {
    this.userService
      .getUsersDropdown(true)
      .pipe(takeUntil(this.onDestroy$))
      .subscribe({
        next: users => {
          this.user$.next(users);
        },
        error: err => {
          console.log(err);
          this.user$.next([]);
        },
      });
  }

  /**
   * Set the form field to search log data
   *
   * @private
   */
  private setSearchFields() {
    this.searchFields$.next([
      {
        key: 'startDateTime',
        type: 'DATE',
        label: _('common.from'),
        getFormattedValue: formValue => {
          return dateFrom(new Date(formValue));
        },
        getFormattedChipValue: formValue =>
          formValue ? formatDate({ date: formValue, format: 'mediumDate' }, this.settingService) : formValue,
        default: new Date(),
      },
      {
        key: 'endDateTime',
        type: 'DATE',
        label: _('common.to'),
        getFormattedValue: formValue => {
          return dateTo(new Date(formValue));
        },
        getFormattedChipValue: formValue =>
          formValue ? formatDate({ date: formValue, format: 'mediumDate' }, this.settingService) : formValue,
        default: new Date(),
      },
      {
        key: 'level',
        type: 'DROPDOWN',
        label: _('common.severity'),
        options: this.severity$,
      },
      {
        key: 'app',
        type: 'DROPSEARCH',
        label: _('title.app'),
        options: this.app$,
        filteredOptions: this.filteredApp$,
        searchForm: this.formApp,
      },
      {
        key: 'search',
        type: 'TEXT',
        label: _('common.message'),
        maxLength: 255,
        minLength: 3,
      },
      {
        key: 'status',
        type: 'DROPDOWN',
        label: _('common.status'),
        options: this.status$,
      },
      {
        key: 'tag',
        type: 'CHIPS',
        label: _('common.tags'),
      },
      {
        key: 'assignee',
        type: 'DROPSEARCH',
        label: _('common.assignee'),
        options: this.user$,
        filteredOptions: this.filteredUser$,
        searchForm: this.formUser,
      },
    ]);
  }

  /**
   * Set the log's data table row menu
   *
   * @private
   */
  private setTableRowMenus() {
    this.rowMenus$.next([
      {
        label: _('common.detail'),
        icon: 'info',
        hasContextMenu: true,
        action: ($e, row) => this.onDetailDialogRow($e, row),
      },
    ]);
  }

  /**
   * Show the form for detail page
   *
   * @param {MouseEvent} event
   * @param {log} row
   * @param {User} row
  //  * @private
   */

  private onDetailDialogRow(event: MouseEvent, row: Log) {
    this.dialog
      .open(DetailLogDialogComponent, {
        width: '80rem',
        panelClass: 'panel-detail-log',
        data: {
          logId: row.id,
        },
      })
      .beforeClosed()
      .pipe(takeUntil(this.onDestroy$))
      .subscribe(res => {
        if (res) {
          this.dataSource.refresh();
        }
      });
  }
}

import { Component, OnInit } from '@angular/core';
import { FormControl } from '@angular/forms';
import { MatDialog } from '@angular/material/dialog';
import { ActivatedRoute } from '@angular/router';
import { marker as _ } from '@biesbjerg/ngx-translate-extract-marker';
import { BehaviorSubject, ReplaySubject, takeUntil } from 'rxjs';
import { RoleService } from 'src/app/administration/services/role.service';
import { SettingService } from 'src/app/administration/services/settings/setting.service';
import { UserService } from 'src/app/administration/services/user.service';
import { LogDataSource } from 'src/app/apps/datasources/log.datasource';
import { Log } from 'src/app/logs/interfaces/log.interface';
import { LogService } from 'src/app/logs/services/logs/log.service';
import { BaseSecurePageComponent } from 'src/app/shared/component/bases/base-secure-page.component';
import { formatDateTime } from 'src/app/shared/helpers/date.helper';
import { SearchFieldOptionValue, SearchFields } from 'src/app/shared/interfaces/search.interface';
import { TableColumns, TableRowMenuItems } from 'src/app/shared/interfaces/table.interface';
import { AppRole } from 'src/app/starter/data/permissions.data';
import { SEARCH_DROPDOWN_SEVERITY } from '../../data/dropdown-config.data';
import { AppSharedService } from '../../services/app-shared/app-shared.service';
import { DetailLogDialogComponent } from '../dialogs/detail-log-dialog/DetailLogDialogComponent';

@Component({
  selector: 'app-log',
  templateUrl: './log.component.html',
  styleUrls: ['./log.component.scss'],
})
export class logComponent extends BaseSecurePageComponent implements OnInit {
  dataSource!: LogDataSource;
  columns$ = new BehaviorSubject<TableColumns>([]);
  rowMenus$ = new BehaviorSubject<TableRowMenuItems>([]);
  searchFields$ = new BehaviorSubject<SearchFields>([]);
  severity$ = new BehaviorSubject<SearchFieldOptionValue[]>([]);
  user$ = new BehaviorSubject<SearchFieldOptionValue[]>([]);
  filteredUser$ = new ReplaySubject<SearchFieldOptionValue[]>(1);
  formUser = new FormControl();

  appId = this.activatedRoute.snapshot.params['id'];

  severity: SearchFieldOptionValue[] = SEARCH_DROPDOWN_SEVERITY;

  /**
   * Get permission to show the app detail page
   *
   * @return {string[]}
   */
  get pagePermissions(): string[] {
    return [AppRole.ROLE_ADMIN, AppRole.ROLE_STANDARD];
  }

  constructor(
    protected override roleService: RoleService,
    private activatedRoute: ActivatedRoute,
    private userService: UserService,
    private settingService: SettingService,
    private dialog: MatDialog,
    private appSharedService: AppSharedService,
    private logService: LogService
  ) {
    super(roleService);

    // data source creation is suggested to be done here
    this.dataSource = new LogDataSource(this.appId, this.logService);
  }

  onInit(): void {
    this.severity$.next(this.severity);
    this.setTableColumns();
    this.setSearchFields();
    this.setTableRowMenus();
    this.fetchUsers();
  }

  /**
   * Set the app detail data table columns
   *
   * @private
   */
  private setTableColumns() {
    this.columns$.next([
      {
        name: 'common.datetime',
        key: 'dateTime',
        getValue: (data: Log) => formatDateTime({ date: data.dateTime }, this.settingService),
      },
      {
        name: 'common.instanceid',
        key: 'instanceId',
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
        name: 'common.assignee',
        key: 'assignee',
      },
    ]);
  }

  /**
   * Set the app detail form field to search user data
   *
   * @private
   */
  private setSearchFields() {
    this.searchFields$.next([
      {
        key: 'level',
        type: 'DROPDOWN',
        label: _('common.severity'),
        options: this.severity$,
      },
      {
        key: 'search',
        type: 'TEXT',
        label: _('common.message'),
        maxLength: 255,
        minLength: 3,
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
   * Set the app detail data table row menu
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
   * Show the page detail app
   *
   * @param {MouseEvent} event
   * @param {log} row
   */
  private onDetailDialogRow(event: MouseEvent, row: Log) {
    this.dialog.open(DetailLogDialogComponent, {
      width: '80rem',
      panelClass: 'panel-detail-log',
      data: {
        logId: row.id,
        appId: this.appId,
      },
    });
  }
}

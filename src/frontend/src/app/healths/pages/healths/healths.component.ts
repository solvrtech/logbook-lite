import { Component, OnInit } from '@angular/core';
import { FormControl } from '@angular/forms';
import { MatDialog } from '@angular/material/dialog';
import { marker as _ } from '@biesbjerg/ngx-translate-extract-marker';
import { BehaviorSubject, ReplaySubject } from 'rxjs';
import { RoleService } from 'src/app/administration/services/role.service';
import { SettingService } from 'src/app/administration/services/settings/setting.service';
import { AppService } from 'src/app/apps/services/apps/app.service';
import { BaseSecurePageComponent } from 'src/app/shared/component/bases/base-secure-page.component';
import { dateFrom, dateTo, formatDate, formatDateTime } from 'src/app/shared/helpers/date.helper';
import { SearchFieldOptionValue, SearchFields } from 'src/app/shared/interfaces/search.interface';
import { TableColumns, TableRowMenuItems } from 'src/app/shared/interfaces/table.interface';
import { AppPage, AppRole } from 'src/app/starter/data/permissions.data';
import { HealthDialogComponent } from '../../../main-menu/components/dialogs/health-dialog/health-dialog.component';
import { AppSharedService } from '../../../main-menu/services/app-shared/app-shared.service';
import { HealthsDataSource } from '../../datasources/healths.datasource';
import { Health } from '../../interfaces/health.interface';
import { HealthService } from '../../services/healths/health.service';

@Component({
  selector: 'app-healths',
  templateUrl: './healths.component.html',
  styleUrls: ['./healths.component.scss'],
})
export class HealthsComponent extends BaseSecurePageComponent implements OnInit {
  dataSource!: HealthsDataSource;
  searchFields$ = new BehaviorSubject<SearchFields>([]);
  status$ = new BehaviorSubject<SearchFieldOptionValue[]>([]);
  app$ = new BehaviorSubject<SearchFieldOptionValue[]>([]);
  columns$ = new BehaviorSubject<TableColumns>([]);
  rowMenus$ = new BehaviorSubject<TableRowMenuItems>([]);
  filteredApp$ = new ReplaySubject<SearchFieldOptionValue[]>(1);

  // Get name page
  override pageName: string = AppPage.APP;

  formApp = new FormControl();

  override get pagePermissions(): string[] {
    return [AppRole.ROLE_ADMIN, AppRole.ROLE_STANDARD];
  }

  status: SearchFieldOptionValue[] = [
    { value: 'ok', label: 'common.ok' },
    { value: 'failed', label: 'common.failed' },
  ];

  constructor(
    protected override roleService: RoleService,
    private healthService: HealthService,
    private settingService: SettingService,
    private dialog: MatDialog,
    private appService: AppService,
    private appSharedService: AppSharedService
  ) {
    super(roleService);
    this.dataSource = new HealthsDataSource(this.healthService);
  }

  onInit(): void {
    this.setSearchFields();
    this.setTableColumns();
    this.setTableRowMenus();
    this.fetchApps();
    this.pageState.state = 'loaded';
    this.status$.next(this.status);
  }

  override ngOnDestroy(): void {
    super.ngOnDestroy();
  }

  /**
   * Set the health's data table columns
   *
   * @private
   */
  private setTableColumns() {
    this.columns$.next([
      {
        name: 'common.name',
        key: 'app',
        logo: (data: Health) => data.appLogo,
        icon: (data: Health) => this.appSharedService.getAppIcon(data.appType),
      },
      {
        name: 'common.datetime',
        key: 'createdAt',
        getValue: (data: Health) => formatDateTime({ date: data.createdAt }, this.settingService),
      },
      {
        name: 'common.replied',
        key: 'status',
        getValue: (data: Health) => (data.status == 'ok' ? 'common.ok' : 'common.failed'),
        badge: (data: Health) =>
          data.status == 'ok' ? 'badge rounded-pill text-success' : 'badge rounded-pill text-danger',
      },
      {
        name: 'common.status',
        key: 'totalFailed',
        total: (data: Health) => (data.totalFailed > 0 ? data.totalFailed : ''),
        getValue: (data: Health) =>
          data.totalFailed == 0 && data.status == 'ok'
            ? 'common.ok'
            : data.status == 'failed'
            ? 'common.failed'
            : 'common.failed',
        badge: (data: Health) =>
          data.totalFailed == 0 && data.status == 'ok'
            ? 'badge rounded-pill text-success'
            : data.totalFailed >= 1 && data.totalFailed < 5
            ? 'badge rounded-pill text-bg-orange'
            : 'badge rounded-pill text-danger',
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
   * Set the form field to search health data/**
   * Set the health's data table row AppRole.ROLE_ADMIN, AppRole.ROLE_STANDARD
   *
   * @private
   */
  private setTableRowMenus() {
    this.rowMenus$.next([
      {
        label: 'common.detail',
        icon: 'info',
        hasContextMenu: false,
        action: ($e, row) => this.onDetailRow($e, row),
        hide: row => row.status === 'failed' || !this.isCurrentUserHas([AppRole.ROLE_ADMIN, AppRole.ROLE_STANDARD]),
      },
    ]);
  }

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
        key: 'status',
        type: 'DROPDOWN',
        label: _('common.replied'),
        options: this.status$,
      },
      {
        key: 'app',
        type: 'DROPSEARCH',
        label: _('title.app'),
        options: this.app$,
        filteredOptions: this.filteredApp$,
        searchForm: this.formApp,
      },
    ]);
  }

  /**
   * Show the dialog for detail health
   *
   * @param {MouseEvent} event
   * @param {Health} row
   * @private
   */
  private onDetailRow(event: MouseEvent, row: Health) {
    this.dialog.open(HealthDialogComponent, {
      width: '70rem',
      data: {
        healthId: row.id,
      },
    });
  }
}

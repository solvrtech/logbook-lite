import { Component, OnInit } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { ActivatedRoute } from '@angular/router';
import { marker as _ } from '@biesbjerg/ngx-translate-extract-marker';
import { BehaviorSubject } from 'rxjs';
import { RoleService } from 'src/app/administration/services/role.service';
import { SettingService } from 'src/app/administration/services/settings/setting.service';
import { HealthDataSource } from 'src/app/apps/datasources/health.datasource';
import { Health } from 'src/app/healths/interfaces/health.interface';
import { BaseSecurePageComponent } from 'src/app/shared/component/bases/base-secure-page.component';
import { dateFrom, dateTo, formatDateTime } from 'src/app/shared/helpers/date.helper';
import { SearchFieldOptionValue, SearchFields } from 'src/app/shared/interfaces/search.interface';
import { TableColumns, TableRowMenuItems } from 'src/app/shared/interfaces/table.interface';
import { AppRole } from 'src/app/starter/data/permissions.data';
import { HealthService } from '../../../healths/services/healths/health.service';
import { HealthDialogComponent } from '../dialogs/health-dialog/health-dialog.component';

@Component({
  selector: 'app-health',
  templateUrl: './health.component.html',
  styleUrls: ['./health.component.scss'],
})
export class HealthComponent extends BaseSecurePageComponent implements OnInit {
  dataSource!: HealthDataSource;
  columns$ = new BehaviorSubject<TableColumns>([]);
  rowMenus$ = new BehaviorSubject<TableRowMenuItems>([]);
  searchFields$ = new BehaviorSubject<SearchFields>([]);
  status$ = new BehaviorSubject<SearchFieldOptionValue[]>([]);
  appId = this.activatedRoute.snapshot.params['id'];

  status: SearchFieldOptionValue[] = [
    { value: 'ok', label: 'common.ok' },
    { value: 'failed', label: 'common.failed' },
  ];

  get pagePermissions(): string[] {
    return [AppRole.ROLE_ADMIN, AppRole.ROLE_STANDARD];
  }

  constructor(
    roleService: RoleService,
    private settingService: SettingService,
    private healthService: HealthService,
    private activatedRoute: ActivatedRoute,
    private dialog: MatDialog
  ) {
    super(roleService);
    this.dataSource = new HealthDataSource(this.healthService, this.appId);
  }

  onInit(): void {
    this.setTableColumns();
    this.setSearchFields();
    this.setTableRowMenus();
    this.status$.next(this.status);
  }

  /**
   * Set the health status data table columns
   *
   * @private
   */
  private setTableColumns() {
    this.columns$.next([
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
   * Set the health status detail data table row menu
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

  /**
   * Set the health status form field to search data
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
      },
      {
        key: 'endDateTime',
        type: 'DATE',
        label: _('common.to'),
        getFormattedValue: formValue => {
          return dateTo(new Date(formValue));
        },
      },
      {
        key: 'status',
        type: 'DROPDOWN',
        label: _('common.replied'),
        options: this.status$,
      },
    ]);
  }

  /**
   * Show the health status detail page
   *
   * @param {MouseEvent} event
   * @param {Health} row
   */
  private onDetailRow(event: MouseEvent, row: Health) {
    this.dialog.open(HealthDialogComponent, {
      width: '70rem',
      data: {
        appId: this.appId,
        healthId: row.id,
      },
    });
  }
}

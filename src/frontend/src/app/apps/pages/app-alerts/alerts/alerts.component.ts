import { Component, OnInit } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { ActivatedRoute, Router } from '@angular/router';
import { marker as _ } from '@biesbjerg/ngx-translate-extract-marker';
import { BehaviorSubject } from 'rxjs';
import { takeUntil } from 'rxjs/operators';
import { RoleService } from 'src/app/administration/services/role.service';
import { SettingService } from 'src/app/administration/services/settings/setting.service';
import { AlertsDataSource } from 'src/app/apps/datasources/alerts.datasource';
import { Alert, AppAlert } from 'src/app/apps/interfaces/alert.interface';
import { AppLogo } from 'src/app/apps/interfaces/app.interface';
import { AppAlertService } from 'src/app/apps/services/app-alerts/app-alert.service';
import { AppService } from 'src/app/apps/services/apps/app.service';
import { AppSharedService } from 'src/app/main-menu/services/app-shared/app-shared.service';
import { BaseSecurePageComponent } from 'src/app/shared/component/bases/base-secure-page.component';
import { ConfirmDialogComponent } from 'src/app/shared/component/confirm-dialog/confirm-dialog.component';
import { formatDateTime } from 'src/app/shared/helpers/date.helper';
import { BreadCrumb, FormPageProps } from 'src/app/shared/interfaces/common.interface';
import { SearchFields } from 'src/app/shared/interfaces/search.interface';
import { TableColumns, TableRowMenuItems } from 'src/app/shared/interfaces/table.interface';
import { AlertsService } from 'src/app/shared/services/alerts/alerts.service';
import { AppRole } from 'src/app/starter/data/permissions.data';

@Component({
  selector: 'app-alerts',
  templateUrl: './alerts.component.html',
  styleUrls: ['./alerts.component.scss'],
})
export class AlertsComponent extends BaseSecurePageComponent implements OnInit {
  dataSource!: AlertsDataSource;
  searchFields = new BehaviorSubject<SearchFields>([]);
  columns = new BehaviorSubject<TableColumns>([]);
  rowMenus = new BehaviorSubject<TableRowMenuItems>([]);
  appId = this.activatedRoute.snapshot.params['id'];
  name!: string;
  hasPermission: boolean = false;

  appLogo!: AppLogo;

  // Breadcrumb for this page
  breadcrumb: BreadCrumb[] = [
    {
      url: '/main-menu/apps',
      label: 'title.apps',
    },
    {
      url: '',
      label: 'common.alerts',
    },
  ];

  /**
   * Get permission to show the alert's page
   *
   * @return {string}
   */
  get pagePermissions(): string[] {
    return [AppRole.ROLE_ADMIN, AppRole.ROLE_STANDARD];
  }

  /**
   * Get permission to create a new alert page
   *
   * @return {boolean}
   */
  get showCreateButton(): boolean {
    return this.isCurrentUserHas([AppRole.ROLE_ADMIN, AppRole.ROLE_STANDARD]);
  }

  // the page's state
  override pageState: FormPageProps = {
    state: 'loading',
    returnUrl: '/main-menu/apps',
  };

  constructor(
    protected override roleService: RoleService,
    private activatedRoute: ActivatedRoute,
    private appAlertService: AppAlertService,
    private alerts: AlertsService,
    private appService: AppService,
    private router: Router,
    private dialog: MatDialog,
    private settingService: SettingService,
    private appSharedService: AppSharedService
  ) {
    super(roleService);

    // data source creation is suggested to be done here
    this.dataSource = new AlertsDataSource(this.appAlertService, this.appId);
  }

  onInit(): void {
    this.setSearchFields();
    this.setTableColumns();
    this.setTableRowMenus();
    this.setName(this.appId);
  }

  /**
   * Set the alert's data table columns
   *
   * @private
   */
  private setTableColumns() {
    this.columns.next([
      { name: 'common.name', key: 'name', badge: (data: Alert) => (data.active == false ? 'inActive' : '') },
      {
        name: 'common.active',
        key: 'active',
        getValue: (data: Alert) => (data.active === true ? 'common.yes' : 'common.no'),
        badge: (data: Alert) => (data.active == false ? 'inActive' : ''),
      },
      {
        name: 'common.source',
        key: 'source',
        getValue: (data: Alert) => (data.source === 'log' ? 'common.logs' : 'common.health_signal'),
        badge: (data: Alert) =>
          data.source == 'log'
            ? 'badge rounded-pill text-bg-primary' + ' ' + (data.active === true ? '' : 'inActive')
            : 'badge rounded-pill text-success' + ' ' + (data.active === true ? '' : 'inActive'),
      },
      {
        name: 'common.last_notified',
        key: 'lastNotified',
        getValue: (data: Alert) =>
          data.lastNotified ? formatDateTime({ date: data.lastNotified }, this.settingService) : '',
        badge: (data: Alert) => (data.active == false ? 'inActive' : ''),
      },
    ]);
  }

  /**
   * Set the alert's data table row menu
   *
   * @private
   */
  private setTableRowMenus() {
    this.rowMenus.next([
      {
        label: 'common.edit',
        icon: 'edit',
        action: ($e, row, newTab, windowFeatures) => this.onEditRow($e, row, newTab, windowFeatures),
        hasContextMenu: true,
        hide: () => !this.isCurrentUserHas([AppRole.ROLE_ADMIN, AppRole.ROLE_STANDARD]) || !this.hasPermission,
      },
      {
        label: 'common.delete',
        icon: 'delete',
        action: ($e, row) => this.onDeleteRow($e, row),
        hide: () => !this.isCurrentUserHas([AppRole.ROLE_ADMIN, AppRole.ROLE_STANDARD]) || !this.hasPermission,
      },
      {
        label: 'common.view',
        icon: 'visibility',
        action: ($e, row, newTab, windowFeatures) => this.onViewAlert($e, row, newTab, windowFeatures),
        hasContextMenu: true,
        hide: () => !this.isCurrentUserHas([AppRole.ROLE_ADMIN, AppRole.ROLE_STANDARD]) || this.hasPermission,
      },
    ]);
  }

  /**
   * Set the form field to search alert data
   *
   * @private
   */
  private setSearchFields() {
    this.searchFields.next([
      {
        key: 'search',
        type: 'TEXT',
        label: _('common.search'),
        maxLength: 255,
        minLength: 3,
      },
    ]);
  }

  /**
   * Fetch app data with the given id to get a name for setting the title alert
   *
   * @param {string} id : app id
   * @private
   */
  private setName(id: string) {
    this.appService.getAppById(id).subscribe({
      next: res => {
        if (res && res.success) {
          this.pageState.state = 'loaded';
          this.name = res.data.name;
          this.hasPermission = res.data.isTeamManager;
          this.appLogo = this.appSharedService.getAppLogo(res.data.type, res.data.appLogo);
        }
      },
      error: err => {
        this.pageState.state = 'unauthorized';
        this.pageState.message = _('common.msg.unauthorized_page');
      },
    });
  }

  /**
   * Show the form for editing page
   *
   * @param {MouseEvent} event
   * @param {User} row
   * @param {boolean} newTab
   * @param {string} windowFeatures
   * @private
   */
  private onEditRow(event: MouseEvent, row: AppAlert, newTab: boolean | undefined, windowFeatures?: string) {
    if (newTab) {
      window.open(`/main-menu/apps/alert/${this.appId}/edit/${row.id}`, '_blank', windowFeatures);
    } else {
      this.router.navigate([`/main-menu/apps/alert/${this.appId}/edit/${row.id}`]);
    }
  }

  /**
   * Show dialog pop`up for delete
   *
   * @param {MouseEvent} event
   * @param {User} row
   * @private
   */
  private onDeleteRow(event: MouseEvent, row: AppAlert) {
    this.dialog
      .open(ConfirmDialogComponent, {
        data: {
          title: 'common.confirmation',
          message: 'common.msg.delete',
        },
      })
      .afterClosed()
      .pipe(takeUntil(this.onDestroy$))
      .subscribe(res => {
        if (res) {
          this.appAlertService.deleteAppAlert(this.appId, row.id).subscribe({
            next: res => {
              if (res && res.success) {
                this.dataSource.refresh();
                this.alerts.setSuccess(_('common.msg.success_deleted'));
              } else {
                this.alerts.setError(_('common.msg.something_went_wrong'));
              }
            },
            error: err => {
              this.alerts.setError(_('common.msg.something_went_wrong'));
            },
          });
        }
      });
  }

  /**
   * Show detail alert page if 'hasPermission' will is true
   *
   * @param {MouseEvent} event
   * @param {showAlert} row
   * @param {boolean | undefined} newTab
   * @param {string} windowFeatures
   */
  private onViewAlert(event: MouseEvent, row: AppAlert, newTab: boolean | undefined, windowFeatures?: string) {
    if (newTab) {
      window.open(`/main-menu/apps/alert/${this.appId}/view/${row.id}`, '_blank', windowFeatures);
    } else {
      this.router.navigate([`/main-menu/apps/alert/${this.appId}/view/${row.id}`]);
    }
  }
}

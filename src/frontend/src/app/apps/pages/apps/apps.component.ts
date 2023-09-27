import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { marker as _ } from '@biesbjerg/ngx-translate-extract-marker';
import { BehaviorSubject } from 'rxjs';
import { RoleService } from 'src/app/administration/services/role.service';
import { AppSharedService } from 'src/app/main-menu/services/app-shared/app-shared.service';
import { BaseSecurePageComponent } from 'src/app/shared/component/bases/base-secure-page.component';
import { SearchFields } from 'src/app/shared/interfaces/search.interface';
import { TableColumns, TableRowMenuItems } from 'src/app/shared/interfaces/table.interface';
import { AppPage, AppRole } from 'src/app/starter/data/permissions.data';
import { AppsDataSource } from '../../datasources/apps.datasource';
import { App } from '../../interfaces/app.interface';
import { AppService } from '../../services/apps/app.service';

@Component({
  selector: 'app-apps',
  templateUrl: './apps.component.html',
  styleUrls: ['./apps.component.scss'],
})
export class AppsComponent extends BaseSecurePageComponent implements OnInit {
  dataSource!: AppsDataSource;
  columns$ = new BehaviorSubject<TableColumns>([]);
  rowMenus$ = new BehaviorSubject<TableRowMenuItems>([]);
  searchFields$ = new BehaviorSubject<SearchFields>([]);

  // Get page name
  override pageName: string = AppPage.APP;

  /**
   * Get permission to show the user`s page
   *
   * @return {string[]}
   */
  get pagePermissions(): string[] {
    return [AppRole.ROLE_ADMIN, AppRole.ROLE_STANDARD];
  }

  /**
   * Get permission to create a new app page
   *
   * @return {boolean}
   */
  get showCreateButton(): boolean {
    return this.isCurrentUserHas([AppRole.ROLE_ADMIN]);
  }

  constructor(
    protected override roleService: RoleService,
    private appService: AppService,
    private router: Router,
    private appSharedService: AppSharedService
  ) {
    super(roleService);

    this.dataSource = new AppsDataSource(this.appService);
  }

  onInit(): void {
    this.setSearchFields();
    this.setTableColumns();
    this.setTableRowMenus();
    this.pageState.state = 'loaded';
    this.appService.selected = 0;
  }

  /**
   * Set the app's data table columns
   *
   * @private
   */
  private setTableColumns() {
    this.columns$.next([
      {
        name: 'common.name',
        key: 'name',
        logo: (data: App) => data.appLogo,
        icon: (data: App) => this.appSharedService.getAppIcon(data.type),
      },
      {
        name: 'app.description',
        key: 'description',
        getValue: (data: App) => {
          let val = data.description.toString();
          return val.length >= 100 ? `${val.substring(0, 100)}...` : val.substring(0, 100);
        },
      },
    ]);
  }

  /**
   * Set the form field to search app data
   *
   * @private
   */
  private setSearchFields() {
    this.searchFields$.next([
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
   * Set the app's data table row menu
   *
   * @private
   */
  private setTableRowMenus() {
    this.rowMenus$.next([
      {
        label: 'common.view',
        icon: 'fullscreen',
        action: ($e, row, newTab, windowFeatures) => this.onViewRow($e, row, newTab, windowFeatures),
        hasContextMenu: true,
        hide: () => !this.isCurrentUserHas([AppRole.ROLE_ADMIN, AppRole.ROLE_STANDARD]),
      },
      {
        label: 'common.setting',
        icon: 'settings',
        action: ($e, row, newTab, windowFeatures) => this.onEditRow($e, row, newTab, windowFeatures),
        hasContextMenu: true,
        hide: (data: App) =>
          !data.isTeamManager ? true : !this.isCurrentUserHas([AppRole.ROLE_ADMIN, AppRole.ROLE_STANDARD]),
      },
      {
        label: 'common.alerts',
        icon: 'notifications_active',
        action: ($e, row, newTab, windowFeatures) => this.onAlertRow($e, row, newTab, windowFeatures),
        hasContextMenu: true,
        hide: (data: App) =>
          !data.isTeamManager ? true : !this.isCurrentUserHas([AppRole.ROLE_ADMIN, AppRole.ROLE_STANDARD]),
      },
    ]);
  }

  /**
   * Show the app log data page
   *
   * @param {MouseEvent} event
   * @param {App} row
   * @param {boolean | undefined} newTab
   * @param {string} windowFeatures
   * @private
   */
  private onViewRow(event: MouseEvent, row: App, newTab: boolean | undefined, windowFeatures?: string) {
    if (newTab) {
      window.open(`/main-menu/apps/view/${row.id}`, '_blank', windowFeatures);
    } else {
      this.router.navigate([`/main-menu/apps/view/${row.id}`]);
    }
  }

  /**
   * Show the form for editing and setting's page
   *
   * @param {MouseEvent} event
   * @param {App} row
   * @param {boolean | undefined} newTab
   * @param {string} windowFeatures
   * @private
   */
  private onEditRow(event: MouseEvent, row: App, newTab: boolean | undefined, windowFeatures?: string) {
    if (newTab) {
      window.open(`/main-menu/apps/settings/${row.id}`, '_blank', windowFeatures);
    } else {
      this.router.navigate([`/main-menu/apps/settings/${row.id}`]);
    }
  }

  /**
   * Show the alert's page
   *
   * @param {MouseEvent} event
   * @param {App} row
   * @param {boolean | undefined} newTab
   * @param {string} windowFeatures
   * @private
   */
  private onAlertRow(event: MouseEvent, row: App, newTab: boolean | undefined, windowFeatures?: string) {
    if (newTab) {
      window.open(`/main-menu/apps/alert/${row.id}`, '_blank', windowFeatures);
    } else {
      this.router.navigate([`/main-menu/apps/alert/${row.id}`]);
    }
  }
}

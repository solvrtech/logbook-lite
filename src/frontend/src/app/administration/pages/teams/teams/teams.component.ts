import { Component, OnInit } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { Router } from '@angular/router';
import { marker as _ } from '@biesbjerg/ngx-translate-extract-marker';
import { BehaviorSubject, takeUntil } from 'rxjs';
import { TEAM_MANAGER, TEAM_STANDARD } from 'src/app/administration/data/role.data';
import { TeamDataSource } from 'src/app/administration/datasources/team.datasource';
import { Team } from 'src/app/administration/interfaces/team.interface';
import { RoleService } from 'src/app/administration/services/role.service';
import { TeamService } from 'src/app/administration/services/team.service';
import { BaseSecurePageComponent } from 'src/app/shared/component/bases/base-secure-page.component';
import { ConfirmDialogComponent } from 'src/app/shared/component/confirm-dialog/confirm-dialog.component';
import { SearchFields } from 'src/app/shared/interfaces/search.interface';
import { TableColumns, TableRowMenuItems } from 'src/app/shared/interfaces/table.interface';
import { AlertsService } from 'src/app/shared/services/alerts/alerts.service';
import { AppRole } from 'src/app/starter/data/permissions.data';

@Component({
  selector: 'app-teams',
  templateUrl: './teams.component.html',
  styleUrls: ['./teams.component.scss'],
})
export class TeamsComponent extends BaseSecurePageComponent implements OnInit {
  dataSource!: TeamDataSource;
  columns$ = new BehaviorSubject<TableColumns>([]);
  rowMenus$ = new BehaviorSubject<TableRowMenuItems>([]);
  searchFields$ = new BehaviorSubject<SearchFields>([]);

  /**
   * Get permission to show the team`s page
   *
   * @return {string}
   */
  get pagePermissions(): string[] {
    return [AppRole.ROLE_ADMIN];
  }

  /**
   * Get permission to create a new team page
   *
   * @return {boolean}
   */
  get showCreateButton(): boolean {
    return this.isCurrentUserHas([AppRole.ROLE_ADMIN]);
  }

  constructor(
    protected override roleService: RoleService,
    private teamService: TeamService,
    private router: Router,
    private dialog: MatDialog,
    private alerts: AlertsService
  ) {
    super(roleService);

    // data source creation is suggested to be done here
    this.dataSource = new TeamDataSource(this.teamService);
  }

  onInit(): void {
    this.setTableColumns();
    this.setSearchFields();
    this.setTableRowMenus();
    this.pageState.state = 'loaded';
  }

  /**
   * Set the team's data table columns
   *
   * @private
   */
  private setTableColumns() {
    this.columns$.next([
      { name: _('common.name'), key: 'name' },
      { name: _('common.members'), key: 'member' },
      {
        name: _('common.membership'),
        key: 'isTeamManager',
        getValue: (data: Team) => (data.isTeamManager ? 'team.' + TEAM_MANAGER : 'team.' + TEAM_STANDARD),
        badge: (data: Team) =>
          data.isTeamManager ? 'badge rounded-pill text-bg-primary' : 'badge rounded-pill text-bg-orange',
      },
    ]);
  }

  /**
   * Set the form field to search team data
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
   * Set the team's data table row menu
   *
   * @private
   */
  private setTableRowMenus() {
    this.rowMenus$.next([
      {
        label: 'common.edit',
        icon: 'edit',
        action: ($e, row, newTab, windowFeatures) => this.onEditRow($e, row, newTab, windowFeatures),
        hasContextMenu: true,
        hide: () => !this.isCurrentUserHas([AppRole.ROLE_ADMIN]),
      },
      {
        label: 'common.detail',
        icon: 'info',
        action: ($e, row, newTab, windowFeatures) => this.onDetailRow($e, row, newTab, windowFeatures),
        hasContextMenu: true,
        hide: () => !this.isCurrentUserHas([AppRole.ROLE_ADMIN]),
      },
      {
        label: 'common.delete',
        icon: 'delete',
        action: ($e, row) => this.onDeleteRow($e, row),
        hasContextMenu: true,
        hide: () => !this.isCurrentUserHas([AppRole.ROLE_ADMIN]),
      },
    ]);
  }

  /**
   * Show the form for editing page
   *
   * @param {MouseEvent} event
   * @param {User} row
   * @param {boolean | undefined} newTab
   * @param {string} windowFeatures
   * @private
   */
  private onEditRow(event: MouseEvent, row: Team, newTab: boolean | undefined, windowFeatures?: string) {
    if (newTab) {
      window.open(`/administration/teams/edit/${row.id}`, '_blank', windowFeatures);
    } else {
      this.router.navigate([`/administration/teams/edit/${row.id}`]);
    }
  }

  /**
   * Show the form for the details
   *
   * @param {MouseEvent} event
   * @param {User} row
   * @param {boolean | undefined} newTab
   * @param {string} windowFeatures
   * @private
   */
  private onDetailRow(event: MouseEvent, row: Team, newTab: boolean | undefined, windowFeatures?: string) {
    if (newTab) {
      window.open(`/administration/teams/details/${row.id}`, '_blank', windowFeatures);
    } else {
      this.router.navigate([`/administration/teams/details/${row.id}`]);
    }
  }

  /**
   * Show dialog pop-up for delete
   *
   * @param {MouseEvent} event
   * @param {User} row
   * @private
   */
  private onDeleteRow(event: MouseEvent, row: Team) {
    if (row.totalApp === 0) {
      this.dialog
        .open(ConfirmDialogComponent, {
          data: {
            title: 'common.confirmation',
            message: 'common.msg.delete',
          },
        })
        .afterClosed()
        .pipe(takeUntil(this.onDestroy$))
        .subscribe({
          next: res => {
            if (res) {
              this.teamService.deleteTeam(row.id).subscribe({
                next: res => {
                  if (res && res.success) {
                    this.dataSource.refresh();
                    this.alerts.setSuccess(_('common.msg.success_deleted'));
                  } else {
                    this.alerts.setError(_('common.msg.something_went_wrong'));
                  }
                },
                error: res => {
                  this.alerts.setError(_('common.msg.something_went_wrong'));
                },
              });
            }
          },
          error: err => {
            this.alerts.setError(_('common.msg.something_went_wrong'));
          },
        });
    } else {
      this.alerts.setError(_('team.msg.delete_error'));
    }
  }
}

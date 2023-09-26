import { Component, OnInit } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { Router } from '@angular/router';
import { marker as _ } from '@biesbjerg/ngx-translate-extract-marker';
import { BehaviorSubject } from 'rxjs';
import { takeUntil } from 'rxjs/operators';
import { ROLE_ADMIN } from 'src/app/administration/data/role.data';
import { UserDataSource } from 'src/app/administration/datasources/user.datasource';
import { User } from 'src/app/administration/interfaces/user.interface';
import { RoleService } from 'src/app/administration/services/role.service';
import { UserService } from 'src/app/administration/services/user.service';
import { AuthService } from 'src/app/login/services/auth/auth.service';
import { BaseSecurePageComponent } from 'src/app/shared/component/bases/base-secure-page.component';
import { ConfirmDialogComponent } from 'src/app/shared/component/confirm-dialog/confirm-dialog.component';
import { SearchFields } from 'src/app/shared/interfaces/search.interface';
import { TableColumns, TableRowMenuItems } from 'src/app/shared/interfaces/table.interface';
import { AlertsService } from 'src/app/shared/services/alerts/alerts.service';
import { AppRole } from 'src/app/starter/data/permissions.data';

@Component({
  selector: 'app-users',
  templateUrl: './users.component.html',
  styleUrls: ['./users.component.scss'],
})
export class UsersComponent extends BaseSecurePageComponent implements OnInit {
  dataSource!: UserDataSource;
  columns$ = new BehaviorSubject<TableColumns>([]);
  rowMenus$ = new BehaviorSubject<TableRowMenuItems>([]);
  searchFields$ = new BehaviorSubject<SearchFields>([]);
  teams: string = '';

  constructor(
    protected override roleService: RoleService,
    private userService: UserService,
    private router: Router,
    private authService: AuthService,
    private dialog: MatDialog,
    private alert: AlertsService
  ) {
    super(roleService);

    // data source creation is suggested to be done here
    this.dataSource = new UserDataSource(this.userService);
  }

  /**
   * Get permission to show the user`s page
   *
   * @return {string}
   */
  get pagePermissions(): string[] {
    return [AppRole.ROLE_ADMIN];
  }

  /**
   * Get permission to create a new user page
   *
   * @return {boolean}
   */
  get showCreateButton(): boolean {
    return this.isCurrentUserHas([AppRole.ROLE_ADMIN]);
  }

  onInit(): void {
    this.setTableColumns();
    this.setSearchFields();
    this.setTableRowMenus();
    this.pageState.state = 'loaded';
  }

  /**
   * Set the user's data table columns
   *
   * @private
   */
  private setTableColumns() {
    this.columns$.next([
      { name: _('common.name'), key: 'name' },
      { name: _('common.email'), key: 'email' },
      {
        name: _('common.role'),
        key: 'role',
        getValue: (data: User) => _(`role.${data.role}`),
        badge: (data: User) =>
          data.role == ROLE_ADMIN ? 'badge rounded-pill text-bg-primary ' : 'badge rounded-pill text-bg-secondary ',
      },
    ]);
  }

  /**
   * Set the user's data table row menu
   *
   * @private
   */
  private setTableRowMenus() {
    this.rowMenus$.next([
      {
        label: _('common.edit'),
        icon: 'edit',
        action: ($e, row, newTab, windowFeatures) => this.onEditRow($e, row, newTab, windowFeatures),
        hasContextMenu: true,
        hide: () => !this.isCurrentUserHas([AppRole.ROLE_ADMIN]),
      },
      {
        label: _('common.delete'),
        icon: 'delete',
        action: ($e, row) => this.onDeleteRow($e, row),
        hide: row => row.id === this.authService.currentUser?.id || !this.isCurrentUserHas([AppRole.ROLE_ADMIN]),
      },
    ]);
  }

  /**
   * Set the form field to search user data
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
   * Show the form for editing page
   *
   * @param {MouseEvent} event
   * @param {User} row
   * @param {boolean} newTab
   * @param {string} windowFeatures
   * @private
   */
  private onEditRow(event: MouseEvent, row: User, newTab: boolean | undefined, windowFeatures?: string) {
    if (newTab) {
      window.open(`/administration/users/edit/${row.id}`, '_blank', windowFeatures);
    } else {
      this.router.navigate([`/administration/users/edit/${row.id}`]);
    }
  }

  /**
   * Delete a user when the user is allowed to delete
   *
   * @param {MouseEvent} event
   * @param {User} row
   * @private
   */
  private onDeleteRow(event: MouseEvent, row: User) {
    this.userService.isAllowedToDelete(row.id).subscribe({
      next: res => {
        this.teams = '';
        if (res.allowedToDelete) {
          this.deleteUser(row.id);
        } else {
          res.teams.forEach((team: any) => {
            this.teams += '"' + team.name + '", ';
          });

          this.alert.setErrorWithData(_('user.msg.delete_error'), this.teams.replace(/,\s*$/, ''));
        }
      },
      error: err => {
        this.alert.setError(_('common.msg.something_went_wrong'));
      },
    });
  }

  /**
   * Show dialog pop`up for delete when the given `userId`
   *
   * @param userId
   */
  private deleteUser(userId: number) {
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
            this.userService.deleteUser(userId).subscribe({
              next: res => {
                if (res) {
                  this.dataSource.refresh();
                  this.alert.setSuccess(_('common.msg.success_deleted'));
                } else {
                  this.alert.setError(_('common.msg.something_went_wrong'));
                }
              },
              error: err => {
                this.alert.setError(_('common.msg.something_went_wrong'));
              },
            });
          }
        },
        error: err => {
          this.alert.setError(_('common.msg.something_went_wrong'));
        },
      });
  }
}

import { Injectable, OnInit } from '@angular/core';

import { marker as _ } from '@biesbjerg/ngx-translate-extract-marker';
import { Observable } from 'rxjs';
import { Assigned } from 'src/app/administration/interfaces/user.interface';
import { RoleService } from 'src/app/administration/services/role.service';
import { AppPage } from 'src/app/starter/data/permissions.data';
import { FormPageProps } from '../../interfaces/common.interface';
import { BasePageComponent } from './base-page.component';

@Injectable()
export abstract class BaseSecurePageComponent extends BasePageComponent implements OnInit {
  currentUserRoles!: String;
  userAssigne: Assigned[] = [];
  pageName!: string;

  pageState: FormPageProps = {
    state: 'loading',
  };

  constructor(protected roleService: RoleService) {
    super();
  }
  /**
   * Return list of permissions that will be allowed to access this page
   */
  abstract get pagePermissions(): string[];

  /**
   * Abstract method that will be called after successfull loading attempt of currently signed-in user.
   * So instead of `ngOnInit()`, use this method.
   */
  abstract onInit(): void;

  /**
   * Will be called first during `ngOnInit()`
   */
  protected onBeforeInit() {}

  /**
   * Will be called if unauthorized result happened during `ngOnInit().
   */
  protected onUnauthorized() {}

  ngOnInit(): void {
    this.onBeforeInit();
    this.loadCurrentUserRoles().subscribe(result => {
      if (result) {
        let found = false;
        this.pagePermissions.every(permission => {
          if (this.roleService.hasPermission(this.currentUserRoles, permission)) {
            found = true;
            return false;
          }
          return true;
        });

        if (found) {
          if (!this.isAssignedTeam() && this.pageName === AppPage.TEAM) {
            this.setAccessTeam();
            this.onUnauthorized();
          } else if (!this.isAssignedApp() && this.pageName === AppPage.APP) {
            this.setAccessApp();
            this.onUnauthorized();
          } else {
            this.onInit();
          }
        } else {
          this.setUnauthorized();
          this.onUnauthorized();
        }
      } else {
        this.setUnauthorized();
        this.onUnauthorized();
      }
    });
  }

  protected setUnauthorized() {
    this.pageState.state = 'unauthorized';
    this.pageState.message = _('common.msg.unauthorized_page');
  }

  protected setAccessApp() {
    this.pageState.state = 'restricted_access';
    this.pageState.message = _('common.msg.restricted_access_app_page');
  }

  protected setAccessTeam() {
    this.pageState.state = 'restricted_access';
    this.pageState.message = _('common.msg.restricted_access_team_page');
  }

  private loadCurrentUserRoles(): Observable<boolean> {
    return new Observable<boolean>(subscriber => {
      this.roleService.getCurrentUserRoles().subscribe({
        next: role => {
          this.currentUserRoles = role.role ?? '';
          this.userAssigne = role.assigned ?? [];
          subscriber.next(true);
        },
        error: () => {
          subscriber.next(false);
        },
      });
    });
  }

  /**
   * Returns true if any of the given `permissions` is found on `currentUserRoles`
   */
  isCurrentUserHas(permissions: string[]): boolean {
    return this.roleService.hasAnyPermission(this.currentUserRoles, permissions);
  }

  /**
   * Returns true if is found on `userAssignee` for Team access
   * @returns {boolean}
   */
  protected isAssignedTeam(): boolean {
    return this.roleService.hasAssignedTeam(this.userAssigne);
  }

  /**
   * Returns true if is found on `userAssignee` for App access
   * @returns {boolean}
   */
  protected isAssignedApp(): boolean {
    return this.roleService.hasAssignedApp(this.userAssigne);
  }
}

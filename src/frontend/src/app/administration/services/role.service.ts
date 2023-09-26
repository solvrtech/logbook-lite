import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { BaseApiService } from 'src/app/shared/component/bases/base-api.service';
import { FormDropdown } from 'src/app/shared/interfaces/common.interface';
import { environment } from 'src/environments/environment';
import { DataRole } from '../interfaces/role.interface';
import { Assigned, User } from '../interfaces/user.interface';

@Injectable({
  providedIn: 'root',
})
export class RoleService extends BaseApiService {
  linkRole!: string;
  constructor(protected override http: HttpClient) {
    super(http);
  }

  getRoles(): Observable<DataRole> {
    return this.get<DataRole>(`${environment.rolesPath}/all-roles`);
  }

  getCurrentUserRoles() {
    return this.get<User>(`${environment.currentUserPath}`);
  }

  hasPermission(roles: String, permissions: string): boolean {
    return roles && roles == permissions;
  }

  hasAnyPermission(roles: String, permissions: string[]): boolean {
    let found = false;
    permissions.every(permission => {
      if (this.hasPermission(roles, permission)) {
        found = true;
        return false;
      }
      return true;
    });
    return found;
  }

  hasAssignedTeam(assigned: Assigned[] | any) {
    let found = false;
    if (assigned.team) {
      found = assigned.team == 1 ? true : false;
    } else {
      assigned.map((res: Assigned) => {
        if (res.team == 1) {
          found = true;
          return false;
        }
        return true;
      });
    }

    return found;
  }

  hasAssignedApp(assigned: Assigned[] | any) {
    let found = false;
    if (assigned.app) {
      found = assigned.app == 1 ? true : false;
    } else {
      assigned.map((res: Assigned) => {
        if (res.app == 1) {
          found = true;
          return false;
        }
        return true;
      });
    }

    return found;
  }

  getRolesDropdown(useBlank: boolean = false): Observable<FormDropdown[]> {
    return new Observable<FormDropdown[]>(subscriber => {
      this.getRoles().subscribe({
        next: roles => {
          const result: FormDropdown[] = roles.roles.map((item: any) => ({
            value: item.role,
            description: `role.${item.role}`,
          }));
          this.linkRole = roles.info;

          // add empty dropdown item
          if (useBlank) {
            result.unshift({
              value: null,
              description: '',
            });
          }
          subscriber.next(result);
          subscriber.complete();
        },
        error: () => {
          subscriber.next([]);
          subscriber.complete();
        },
      });
    });
  }
}

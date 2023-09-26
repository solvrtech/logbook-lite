import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';
import { RoleService } from 'src/app/administration/services/role.service';
import { menu, MenuItems } from '../../data/menu.data';

@Injectable({
  providedIn: 'root',
})
export class MenuService {
  menus$ = new BehaviorSubject([]);
  constructor(private roleService: RoleService) {}

  refresh() {
    this.roleService.getCurrentUserRoles().subscribe(roles => {
      const menus: any = menu;
      menus.forEach((menuItem: MenuItems) => {
        this.traverseAndHide(roles.role ?? '', menuItem);
      });
      this.menus$.next(menus);
    });
  }

  private traverseAndHide(roles: String, menuItem: MenuItems) {
    // recursively traverse and hide disallowed menus
    if (menuItem.items) {
      menuItem.items.forEach(child => {
        return this.traverseAndHide(roles, child);
      });
    }

    if (
      menuItem.permissions == null ||
      (menuItem.link && this.roleService.hasAnyPermission(roles, menuItem.permissions))
    ) {
      menuItem.hidden = false;
    } else {
      menuItem.hidden = true;
    }

    // check if there is no visible first-level child, then hides it
    if (menuItem.items && !menuItem.items.find(child => !child.hidden)) {
      menuItem.hidden = true;
    }
  }
}

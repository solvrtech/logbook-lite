import { marker as _ } from '@biesbjerg/ngx-translate-extract-marker';
import { AppRole } from './permissions.data';

export interface MenuItems {
  /** unique identifier for this menu item */
  id?: string;

  /** displayed label of this menu */
  label: string;

  /** mandatory only to menu items */
  link?: string;

  /** mat-icon string for this menu */
  icon?: string;

  /** mandatory only for nested menu container */
  items?: MenuItems[];

  /** mandatory only for nested menu container (where `items` are not empty) */
  prefix?: string;

  /** set true if this is just a heading title */
  asHeading?: boolean;

  /** CSS classes for this menu (ex: to be used to set 'active' class) */
  cssClasses?: string;

  /** show/hide this menu */
  hidden?: boolean;

  /** needed permission(s) to see this menu */
  permissions?: string[];
}

export type Menu = MenuItems[];

// Administration
const menuAdministration: MenuItems = {
  id: 'administration',
  label: _('title.administration'),
  icon: 'manage_accounts',
  prefix: '/administration',
  items: [
    {
      id: 'administration_users',
      label: _('title.users'),
      icon: 'account_box',
      link: '/administration/users',
      permissions: [AppRole.ROLE_ADMIN],
    },
    {
      id: 'administration_teams',
      label: _('title.teams'),
      icon: 'group',
      link: '/administration/teams',
      permissions: [AppRole.ROLE_ADMIN],
    },
    {
      id: 'administration_settings',
      label: _('title.settings'),
      icon: 'settings_applications',
      link: '/administration/settings',
      permissions: [AppRole.ROLE_ADMIN],
    },
  ],
};

// Main menu
const menuApps: MenuItems = {
  id: 'main_menu',
  label: _('title.main_menu'),
  icon: 'devices',
  prefix: '/main-menu',
  items: [
    {
      id: 'mainMenu_logs',
      label: _('title.logs'),
      icon: 'library_books',
      link: '/main-menu/logs',
      permissions: [AppRole.ROLE_ADMIN, AppRole.ROLE_STANDARD],
    },
    {
      id: 'mainMenu_health',
      label: _('title.health_status'),
      icon: 'monitor_heart',
      link: '/main-menu/healths',
      permissions: [AppRole.ROLE_ADMIN, AppRole.ROLE_STANDARD],
    },
    {
      id: 'mainMenu_apps',
      label: _('title.apps'),
      icon: 'devices',
      link: '/main-menu/apps',
      permissions: [AppRole.ROLE_ADMIN, AppRole.ROLE_STANDARD],
    },
    {
      id: 'mainMenu_myTeams',
      label: _('title.my_teams'),
      icon: 'diversity_3',
      link: '/main-menu/my-teams',
      permissions: [AppRole.ROLE_STANDARD],
    },
  ],
};

const menus = [];
menus.push(menuApps);
menus.push(menuAdministration);

export const menu: Menu = menus;

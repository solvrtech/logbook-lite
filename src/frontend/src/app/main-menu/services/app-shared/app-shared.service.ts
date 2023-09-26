import { Injectable } from '@angular/core';
import {
  APP_TYPE_GENERAL,
  APP_TYPE_LARAVEL,
  APP_TYPE_SYMFONY,
  APP_TYPE_WORDPRESS,
} from 'src/app/shared/interfaces/common.interface';
import { AppLogo } from '../../../apps/interfaces/app.interface';
import { ALERT, CRITICAL, EMERGENCY, ERROR, INFO, NOTICE, WARNING } from '../../data/severity.data';

@Injectable({
  providedIn: 'root',
})
export class AppSharedService {
  constructor() {}

  /**
   * Get logo for app detail data
   *
   * @param {string} type
   * @param {string} logo
   * @returns
   */
  getAppLogo(type: string, logo: string): AppLogo {
    return {
      type: logo ? 'image' : 'icon',
      value: logo ? logo : this.getAppIcon(type),
    };
  }

  /**
   * Get icon for app data when logo null
   *
   * @param {string} type
   * @return
   */
  getAppIcon(type: string) {
    let icon = '';

    if (type == APP_TYPE_SYMFONY) {
      icon = 'devicon-symfony-original colored';
    } else if (type == APP_TYPE_LARAVEL) {
      icon = 'devicon-laravel-plain colored';
    } else if (type == APP_TYPE_WORDPRESS) {
      icon = 'devicon-wordpress-plain colored';
    } else if (type == APP_TYPE_GENERAL) {
      icon = 'general-app';
    }

    return icon;
  }

  getBagdeAppLogLevel(level: string) {
    let bagde = '';

    level == EMERGENCY
      ? (bagde = 'badge rounded-pill text-bg-orange')
      : level == ALERT
      ? (bagde = 'badge rounded-pill text-bg-info')
      : level == CRITICAL
      ? (bagde = 'badge rounded-pill text-bg-dark')
      : level == ERROR
      ? (bagde = 'badge rounded-pill text-danger')
      : level == WARNING
      ? (bagde = 'badge rounded-pill text-warning')
      : level == NOTICE
      ? (bagde = 'badge rounded-pill text-bg-secondary')
      : level == INFO
      ? (bagde = 'badge rounded-pill text-bg-primary')
      : (bagde = 'badge rounded-pill text-success');

    return bagde;
  }
}

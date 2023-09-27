import { formatDate as angularFormatDate } from '@angular/common';
import { SettingService } from 'src/app/administration/services/settings/setting.service';
import { getCurrentLocale } from './locale.helper';

interface FormatTableDateOptions {
  date: string | Date | number;
  format?: string;
  locale?: string;
  timezone?: string;
}

export function formatDate(config: FormatTableDateOptions, settingService: SettingService): string {
  const format = config.format ? config.format : 'mediumDate';
  const locale = config.locale ? config.locale : getCurrentLocale(settingService);
  const timezone = config.timezone ? config.timezone : getBrowserGMTOffset();

  return angularFormatDate(config.date, format, locale, timezone);
}

export function formatDateTime(config: FormatTableDateOptions, settingService: SettingService): string {
  const format = config.format ? config.format : 'medium';
  const locale = config.locale ? config.locale : getCurrentLocale(settingService);
  const timezone = config.timezone ? config.timezone : getBrowserGMTOffset();

  return angularFormatDate(config.date, format, locale, timezone);
}

export function getBrowserGMTOffset(prefix: string = 'GMT') {
  const tzOffsetInMinutes = new Date().getTimezoneOffset(); // locale zone - GMT
  const sign = -1 * tzOffsetInMinutes < 0 ? '-' : '+';
  const tzOffset = Math.floor(Math.abs(tzOffsetInMinutes) / 60);
  const tzOffsetString = tzOffset < 10 ? `0${tzOffset}00` : `${tzOffset}00`;

  return `${prefix}${sign}${tzOffsetString}`;
}

export function dateFormat(date: any) {
  const day = (date && date.getDate()) || -1;
  const dayWithZero = day.toString().length > 1 ? day : '0' + day;
  const month = (date && date.getMonth() + 1) || -1;
  const monthWithZero = month.toString().length > 1 ? month : '0' + month;
  const year = (date && date.getFullYear()) || -1;
  const hours = new Date(date.setHours(0));

  return `${year}-${monthWithZero}-${dayWithZero}`;
}

export function dateFrom(date: any) {
  const day = (date && date.getDate()) || -1;
  const dayWithZero = day.toString().length > 1 ? day : '0' + day;
  const month = (date && date.getMonth() + 1) || -1;
  const monthWithZero = month.toString().length > 1 ? month : '0' + month;
  const year = (date && date.getFullYear()) || -1;

  // Time
  const hours = new Date(date.setHours(0, 0, 0));
  const hoursWithZero = hours.getHours() > 1 ? hours.getHours() : '0' + hours.getHours();
  const minuteWithZero = hours.getMinutes() > 1 ? hours.getMinutes() : '0' + hours.getMinutes();
  const secondWithZero = hours.getSeconds() > 1 ? hours.getSeconds() : '0' + hours.getSeconds();

  return `${year}-${monthWithZero}-${dayWithZero} ${hoursWithZero}:${minuteWithZero}:${secondWithZero}`;
}

export function dateTo(date: any) {
  const day = (date && date.getDate()) || -1;
  const dayWithZero = day.toString().length > 1 ? day : '0' + day;
  const month = (date && date.getMonth() + 1) || -1;
  const monthWithZero = month.toString().length > 1 ? month : '0' + month;
  const year = (date && date.getFullYear()) || -1;

  // Time
  const hours = new Date(date?.setHours(23, 59, 59));
  const hoursWithZero = hours.getHours() > 1 ? hours.getHours() : '0' + hours.getHours();
  const minuteWithZero = hours.getMinutes() > 1 ? hours.getMinutes() : '0' + hours.getMinutes();
  const secondWithZero = hours.getSeconds() > 1 ? hours.getSeconds() : '0' + hours.getSeconds();

  return `${year}-${monthWithZero}-${dayWithZero} ${hoursWithZero}:${minuteWithZero}:${secondWithZero}`;
}

export function dateTime(config: FormatTableDateOptions) {
  const format = config.format ? config.format : 'YYYY-MM-dd HH:mm:ss';
  const locale = config.locale ? config.locale : 'en-Us';
  const timezone = config.timezone ? config.timezone : getBrowserGMTOffset();

  return angularFormatDate(config.date, format, locale, timezone);
}

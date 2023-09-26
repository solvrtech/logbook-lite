import { formatNumber } from '@angular/common';
import { HttpParams } from '@angular/common/http';
import { FormControl, FormGroup, FormGroupDirective, NgForm } from '@angular/forms';
import { ErrorStateMatcher } from '@angular/material/core';
import { Params } from '@angular/router';
import { SettingService } from 'src/app/administration/services/settings/setting.service';
import { SearchAttemptFields } from '../interfaces/search.interface';
import { formatDate } from './date.helper';
// import { SearchAttemptFields } from '../components/search-table/search-table.component';

export class MyErrorStateMatcher implements ErrorStateMatcher {
  isErrorState(control: FormControl | null, form: FormGroupDirective | NgForm | null): boolean {
    const isSubmitted = form && form.submitted;
    return !!(control && control.invalid && (control.dirty || control.touched || isSubmitted));
  }
}

/**
 * Returns true if the given single or array of FormGroups are valid, or false otherwise.
 * @param forms array of FormGroup to be checked.
 */
export function isFormValid(...forms: FormGroup[]): boolean {
  for (const form of forms) {
    if (!form.valid) {
      return false;
    }
  }
  return true;
}

/**
 * Returns true if the given obj is empty (as an Object or array)
 * @param obj Object or array.
 */
export function isEmpty(obj: any): boolean {
  if (obj.constructor === Object) {
    return Object.keys(obj).length === 0;
  } else if (Array.isArray(obj)) {
    return !obj.length;
  }
  return false;
}

/**
 * Filter data (or do "search") on frontend side.
 * TODO: to filter 'DATE' type of field.
 *
 * @param data array of objects that being received from backend
 * @param filterFields SearchAttemptFields that has been set through SearchService, for example on SearchTableComponent.
 */
export function filterSearchResult(data: any, filterFields?: SearchAttemptFields): any {
  if (filterFields) {
    return data.filter((item: any) => {
      for (const filterField of filterFields.fields) {
        if (filterField.value) {
          if (!item.hasOwnProperty(filterField.field.key)) {
            return false;
          }

          if (filterField.field.type === 'TEXT' || filterField.field.type === 'DROPDOWN') {
            if (item[filterField.field.key].toLowerCase().indexOf(filterField.value.toLowerCase()) === -1) {
              return false;
            }
          } else if (filterField.field.type === 'CHECKBOX') {
            if (item[filterField.field.key] !== filterField.value) {
              return false;
            }
          }
        }
      }

      return true;
    });
  }
  return data;
}

/**
 * Return HttpParams from the given SearchAttemptFields
 * @param filterFields SearchAttemptFields containing search filters
 * @param params HttpParams, if null then new instance will be created
 */
export function buildHttpParams(filterFields: SearchAttemptFields, params?: HttpParams) {
  if (params == null) {
    params = new HttpParams();
  }
  filterFields.fields.forEach(field => {
    if (field.value) {
      params = params?.set(field.field.key, field.value);
    }
  });
  return params;
}

/**
 * Convert to hex from byte64
 *
 * @param base64Value as byte64 string
 */
export function hexFromBase64(base64Value: string): string {
  return window
    .atob(base64Value)
    .split('')
    .map(function (aChar) {
      return ('0' + aChar.charCodeAt(0).toString(16)).slice(-2);
    })
    .join('');
}

/**
 * Convert to byte64 string from hex
 *
 * @param hexValue as hex string
 */
export function hexToBase64(hexValue: any): any {
  return btoa(
    String.fromCharCode.apply(
      null,
      hexValue
        .replace(/[\r\n]/g, '')
        .replace(/([\da-fA-F]{2}) ?/g, '0x$1 ')
        .replace(/ +$/, '')
        .split(' ')
    )
  );
}

/**
 * Convert string to payment method translatable text
 * @param val string, value to be convert
 */
export function translatablePaymentMethodType(val: string): string {
  return `payment_method.type.${val.trim().replace(/\s+/g, '_').toLowerCase()}`;
}

/**
 * Format Number base on language
 * @param val number to be formatted
 * @param lang current locale code
 */
export function formatNumberDefault(val: number, locale: string, digitsInfo: string = '1.2-2'): string {
  return formatNumber(val, locale, digitsInfo);
}

/**
 * Check if the the given value is undefined or null, then return defaultVal.
 * Returns the same given value if it is defined.
 * @param value variable to be checked for undefined or null
 * @param defaultVal value to be returned if the given value is undefined or null
 */
export function defaultValue(value: any, defaultVal: any) {
  if (typeof value === 'undefined' || value === null) {
    return defaultVal;
  }
  return value;
}

/**
 * Returns formatted file name to be used for all exports
 * @param filename
 */
export function getFormattedFileName(filename: string, settingService: SettingService): string {
  const name = filename ? filename.replace(/\//g, '-') : '';
  const date = formatDate({ date: Date(), format: 'y-MM-dd HH-mm-ss' }, settingService);
  return `${name}_${date}`;
}

export function randomizeArray(input: any[]): any[] {
  // Create a copy of the original array to be randomized
  let shuffle = [...input];

  // Defining function returning random value from i to N
  const getRandomValue = (i: any, N: number) => Math.floor(Math.random() * (N - i) + i);

  // Shuffle a pair of two elements at random position j
  shuffle.forEach((elem, i, arr, j = getRandomValue(i, arr.length)) => ([arr[i], arr[j]] = [arr[j], arr[i]]));

  return shuffle;
}

export function slugify(...args: (string | number)[]): string {
  const value = args.join(' ');

  return value
    .normalize('NFD') // split an accented letter in the base letter and the acent
    .replace(/[\u0300-\u036f]/g, '') // remove all previously split accents
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9 ]/g, '') // remove all chars not letters, numbers and spaces (to be replaced)
    .replace(/\s+/g, '_'); // separator
}

/**
 * Returns URL with query params has been modified with the given `params`
 */
export function appendQueryParams(params: Params): URL {
  const theUrl = new URL(window.location.href);
  let searchParams = theUrl.searchParams;
  Object.keys(params).forEach(key => searchParams.set(key, params[key]));
  return theUrl;
}

/**
 * Returns URL with cleared query params with keys as given
 */
export function clearQueryParams(keys: string[]): URL {
  const params: Params = {};
  keys.forEach(key => (params[key] = null));
  return appendQueryParams(params);
}

export function fetchQueryParams(keys: string[]): Params | null {
  let searchParams = new URL(window.location.href).searchParams;
  const params: Params = {};
  searchParams.forEach((value, key) => {
    // only defined key inside `keys` will be appended
    if (keys.indexOf(key) != -1) {
      params[key] = value;
    }
  });
  return params;
}

/**
 * Returns value of given `key` from URL query params
 */
export function fetchQueryParamValue(key: string): string | null {
  let searchParams = new URL(window.location.href).searchParams;
  return searchParams.get(key);
}

/**
 * Convert the given object into FormData
 */
export function objectToFormData(object: { [key: string]: any }): FormData {
  const formData = new FormData();
  for (const [key, value] of Object.entries(object)) {
    if (value instanceof File || value instanceof Blob) {
      formData.set(key, value);
    } else {
      formData.set(key, typeof value === 'object' ? JSON.stringify(value) : value);
    }
  }
  return formData;
}

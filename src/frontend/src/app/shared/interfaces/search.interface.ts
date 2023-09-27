import { FormControl, ValidatorFn } from '@angular/forms';
import { Observable, ReplaySubject } from 'rxjs';

/**
 * Represents each search field or parameter that is being displayed.
 */
export interface SearchField {
  /** The key of the searched column on database */
  key: string;
  /** Displayed label for the search field */
  label: string;
  /** Default value for this search param */
  default?: string | boolean | object;
  /** Search fields hint */
  hint?: string;
  /** Type of search parameter */
  type: 'TEXT' | 'DROPDOWN' | 'CHECKBOX' | 'DATE' | 'DROPSEARCH' | 'CHIPS';
  /** Only for type = "TEXT", max characters allowed on input field */
  maxLength?: number;

  minLength?: number;
  /** only for type = DROPDOWN, multiple selection will allowed if multiple = true */
  multipleValue?: boolean;
  /** only for 'DROPDOWN' type */
  options?: Observable<SearchFieldOptionValue[]>;
  /** Array of validators for the search field */
  validators?: ValidatorFn[];
  /** if set, then this will be called when building SearchAttemptFields */
  getFormattedValue?: (formValue: any) => string;
  /** if set, then this will be called when showing chips */
  getChipValue?: (formValue: any) => string;
  /** if set, then this will be called instead of getChipValue() */
  getFormattedChipValue?: (formValue: any) => string;
  /** if set, the returned value will show/hide the clear icon on the chip */
  isChipClearable?: (formValue: any) => boolean;
  /** set to hex */
  toHex?: boolean;
  /** show or hide this search field */
  hide?: boolean;

  filteredOptions?: ReplaySubject<SearchFieldOptionValue[]>;

  searchForm?: FormControl | any;
}

/**
 * SearchField value for 'DROPDOWN' field type.
 */
export interface SearchFieldOptionValue {
  value: string;
  label: string;
}

/**
 * Represents visible search fields
 */
export interface SearchFields extends Array<SearchField> {}

/**
 * Search field that is used on search attempt
 */
export interface SearchAttemptField {
  field: SearchField;
  value: any;
}

/**
 * Represents search fields that are being used for search
 */
export interface SearchAttemptFields {
  /** Search parameters in URL query parameters format */
  query: string;
  /** Search parameters in semicolon separated format */
  pattern: string;
  /** Search parameters in DataSearchParams format, for example to be used for export */
  params: DataSearchParams;
  fields: SearchAttemptField[];
}

export interface DataSearchParams {
  raw: { [key: string]: string };
  formatted: {
    [key: string]: {
      label: string;
      value: string;
    };
  };
}

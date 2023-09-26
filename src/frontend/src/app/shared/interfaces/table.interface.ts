import { TemplateRef } from '@angular/core';
import { MatSlideToggleChange } from '@angular/material/slide-toggle';

/**
 * Table's row menu items
 */
export interface TableRowMenuItem<DATA = any> {
  /**
   * the menu's label
   */
  label: string;

  /**
   * menu's icon, taken from Material icon
   */
  icon: string;

  /**
   * menu's on click action
   */
  action: ($event: MouseEvent, row: DATA, target?: boolean, windowFeatures?: string) => void;

  /**
   * Show menu's context menu or not (ex: to open in new tab)
   */
  hasContextMenu?: boolean | ((row: DATA) => boolean);

  /**
   * hide action if function evaluets to true
   */
  hide?: (row: DATA) => boolean;
}

/**
 * Table's column items
 */
export interface TableColumn<DATA = any> {
  /**
   * header column label
   */
  name: string;

  /**
   * key of column value
   */
  key: string;

  total?: (data: DATA) => any;

  /**
   * 'text' display type will be used if not set
   * 'html' display type will render any html code
   * 'template' display will render ref that passed to component
   */
  type?: 'text' | 'chips' | 'html' | 'toggle' | 'template';

  /** cell content alignment (default = '') */
  align?: 'left' | 'center' | 'right' | 'start' | 'end';

  // Badge for status
  badge?: (data: DATA) => string;

  // Action for role
  action?: ($event: MouseEvent) => void;

  // An event will be dispatched each time the slide-toggle changes its value.
  toggleChange?: (event: MatSlideToggleChange, row: DATA) => void;

  // required if type='chips'
  chipKey?: string;

  /**
   * key for footer column's value (optional)
   */
  footerKey?: string;

  /**
   * When set, this will be used instead the actual footer value (optional)
   */
  footerDefaultValue?: string;

  /**
   * Get the formatted value
   * @param data The value to be formatted
   */
  getValue?: (data: DATA) => string | undefined;

  /**
   * Get formatted footer value
   * @param data The value to be formatted
   */
  getFooterValue?: (data: DATA) => string;

  /**
   * Get CSS classes for the current displayed cell
   * @param data current row
   * @param rowIndex cell's row index (zero based)
   * @param colIndex cell's column index (zero based)
   */
  getCellClasses?: (data: DATA, rowIndex: number, colIndex: number) => string;

  /** Optional CSS classes for the column's header */
  headerClasses?: string;

  /** Optional CSS classes for the column's footer */
  footerClasses?: string;

  /**
   * getter template for column.<br/>
   * Parameter for template are : <ol>
   *   <li>rowData = data for current row</li>
   *   <li>key key of column. same as field key</li>
   *   <li>value value from current row , for current column</li>
   *   </ol>
   * @param data  current data row
   */
  getTemplate?: (data: DATA) => TemplateRef<any>;

  /**
   * Hide table's cell if needed
   */
  hide?: (row: DATA) => boolean;

  /** Optional CSS classes for the column's icon */
  icon?: (row: DATA) => string;

  /** App logo for the column's */
  logo?: (row: DATA) => string;
}

export interface TableFooterColumn {
  /**
   * key of column value
   */
  key: string;
}

export interface TableColumns extends Array<TableColumn> {}

export interface TableRowMenuItems<DATA = any> extends Array<TableRowMenuItem<DATA>> {}

export interface TableRowClass {
  index: number;
  classes: string;
}

export interface TableRowClasses extends Array<TableRowClass> {}

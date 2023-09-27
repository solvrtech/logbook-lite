import { SelectionModel } from '@angular/cdk/collections';
import { Location } from '@angular/common';
import {
  AfterViewInit,
  ChangeDetectionStrategy,
  ChangeDetectorRef,
  Component,
  EventEmitter,
  Input,
  OnDestroy,
  OnInit,
  Output,
  QueryList,
  TemplateRef,
  ViewChild,
  ViewChildren,
} from '@angular/core';
import { MatCheckbox } from '@angular/material/checkbox';
import { MatMenuTrigger } from '@angular/material/menu';
import { MatPaginator } from '@angular/material/paginator';
import { ActivatedRoute, Params, Router } from '@angular/router';
import { marker as _ } from '@biesbjerg/ngx-translate-extract-marker';
import { Observable, Subject, merge } from 'rxjs';
import { repeat, takeUntil, tap } from 'rxjs/operators';
import { appendQueryParams } from '../../helpers/helpers.helper';
import { SearchAttemptFields } from '../../interfaces/search.interface';
import {
  TableColumn,
  TableColumns,
  TableRowClasses,
  TableRowMenuItem,
  TableRowMenuItems,
} from '../../interfaces/table.interface';
import { AutoRefreshService } from '../../services/auto-refresh/auto-refresh.service';
import { TableDataSource } from '../bases/table.datasource';

@Component({
  selector: 'app-table',
  templateUrl: './table.component.html',
  styleUrls: ['./table.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class TableComponent implements OnInit, AfterViewInit, OnDestroy {
  private onDestroy$ = new Subject<void>();

  @Input() realtimeRefresh = false;
  @Input() columns$!: Observable<TableColumns>;
  @Input() dataSource!: TableDataSource<any>;
  @Input() rowMenus$!: Observable<TableRowMenuItems>;
  @Input() showFooter = false;
  @Input() showMenus = false;
  @Input() filename?: string;
  @Input() createLink?: string;
  @Input() queryParams?: Params;
  @Input() isResponsive = true;
  @Input() responsiveNow = false;
  @Input() noDataClass = 'mat-card mat-elevation-z0 no-data-card';
  @Input() tableWrapperClasses?: string;
  @Input() noDataText = _('common.no_data');
  @Input() rowClasses!: (index: number, row: any) => string;

  /** Set default page index */
  @Input() setPage?: number;

  /** template area for buttons on the right side of table's top toolbar */
  @Input() rightButtonsTemplate!: TemplateRef<any>;

  /** template area for buttons on the left side of table's top toolbar */
  @Input() leftButtonsTemplate!: TemplateRef<any>;

  /** forcefully show or hide table's top toolbar */
  @Input() useToolbar = true;

  /** default query params name for current page */
  @Input() pageParamName = 'page';

  /** To load data on init */
  @Input() loadDataOnInit = true;

  /** Hides component using CSS style (display: none) */
  @Input() visible = true;

  /** Uses row selection or not. When used, a radio button will be shown */
  @Input() useSelection = false;

  @Input() checkBoxMenu = false;

  @Input() selected = false;

  /** Emits currently selected row data when `useSelection` is true */
  @Output() rowSelected = new EventEmitter<any>();
  @Output() markAllSelected = new EventEmitter<any>();

  @ViewChildren('checkBox') checkBox!: QueryList<any>;
  @ViewChildren('checkBoxx') checkBoxx!: QueryList<any>;
  @ViewChild(MatPaginator, { static: true }) paginator!: MatPaginator;

  selection = new SelectionModel<any>(true);

  filter: any;

  columnDefs: TableColumns = [];
  inputRowMenus: TableRowMenuItems = [];
  tableRowClasses: TableRowClasses = [];

  displayedColumns: string[] = [];
  pageSizeOptions = [25, 50, 100];
  dataCount = 0;
  showTable = false;
  usePaging = true;
  searchRealtime = true;
  isRealtime: boolean = false;

  rows: any;
  footer: any = {};

  stopDataSourceSubscription$ = new Subject<void>();
  resumeDataSourceSubscription$ = new Subject<void>();

  @ViewChild(MatMenuTrigger, { static: true }) contextMenu!: MatMenuTrigger;

  contextMenuPosition = { x: '0px', y: '0px' };

  get showToolbar(): boolean {
    return this.useToolbar ? this.leftButtonsTemplate != null || this.rightButtonsTemplate != null : false;
  }

  constructor(
    private cdr: ChangeDetectorRef,
    private location: Location,
    private router: Router,
    private activatedRoute: ActivatedRoute,
    private autoRefreshService: AutoRefreshService
  ) {}

  ngOnInit() {
    if (!this.dataSource) {
      return;
    }

    this.subscribeAutoRefresh();
    this.reactToQueryParams();
    this.usePaging = this.dataSource.isPaginated;
    this.setWrapperClasses();

    this.columns$.pipe(takeUntil(this.onDestroy$)).subscribe(columns => {
      this.columnDefs = columns;
      this.displayedColumns = columns.map(c => c.key);

      if (this.useSelection) {
        this.displayedColumns.unshift('selectRow');
      }

      if (this.checkBoxMenu) {
        this.displayedColumns.unshift('check-box');
      }

      if (this.showMenus) {
        this.displayedColumns.push('menus');
      }
      this.cdr.markForCheck();
    });

    if (this.rowMenus$) {
      this.rowMenus$.pipe(takeUntil(this.onDestroy$)).subscribe(rowMenus => {
        this.inputRowMenus = rowMenus;
        this.cdr.markForCheck();
      });
    }

    this.dataSource
      .connect()
      .pipe(
        takeUntil(merge(this.onDestroy$, this.stopDataSourceSubscription$)),
        repeat({ delay: () => this.resumeDataSourceSubscription$ })
      )
      .subscribe(data => {
        this.rows = data;
        this.fillTableRowClasses(data);
      });

    this.dataSource
      .countObservable()
      .pipe(
        takeUntil(merge(this.onDestroy$, this.stopDataSourceSubscription$)),
        repeat({ delay: () => this.resumeDataSourceSubscription$ })
      )
      .subscribe(count => {
        this.dataCount = count;
        this.showTable = count >= 1;
        this.cdr.markForCheck();
      });

    this.dataSource
      .footerObservable()
      .pipe(
        takeUntil(merge(this.onDestroy$, this.stopDataSourceSubscription$)),
        repeat({ delay: () => this.resumeDataSourceSubscription$ })
      )
      .subscribe(data => {
        if (!this.isEmpty(data)) {
          // show table if footer has data
          this.showTable = true;
          this.footer = data;
          this.cdr.markForCheck();
        }
      });

    if (this.loadDataOnInit) {
      this.loadData();
    }
  }

  isAllSelected() {
    const numSelected = this.selection.selected.length;
    const numRows = this.rows.length;

    return numSelected === numRows;
  }

  allSelected(MatCheckbox: MatCheckbox | any) {
    if (MatCheckbox.checked) {
      this.isAllSelected() ? this.selection.clear() : this.rows.forEach((row: any) => this.selection.select(row));
    } else {
      this.selection.clear();
    }
  }

  private isEmpty(object: any): boolean {
    if (object.constructor === Object) {
      return Object.keys(object).length === 0;
    } else if (Array.isArray(object)) {
      return !object.length;
    }
    return false;
  }

  ngAfterViewInit(): void {
    // react to paginator's page observable and reload data when user clicks
    // the navigation buttons
    if (this.usePaging) {
      this.paginator?.page.pipe(tap(() => this.loadData())).subscribe();
    }
  }

  private loadData() {
    if (this.dataSource) {
      this.selection.clear();
      if (this.dataSource.isPaginated) {
        /**
         * if setPage is existed, change pagination index and delete setPage
         */
        if (typeof this.setPage !== 'undefined') {
          this.paginator.pageIndex = this.setPage;
          delete this.setPage;
        }
        this.dataSource.fetch({
          pageIndex: (this.paginator?.pageIndex ?? 0) + 1,
          pageSize: this.paginator.pageSize || this.pageSizeOptions[0],
          filter: this.filter,
        });
        this.appendPageParamToUrl();
      } else {
        this.dataSource.loadData({
          filter: this.filter,
        });
      }
    }
  }

  private appendPageParamToUrl() {
    const url = appendQueryParams({
      [this.pageParamName]: this.paginator?.pageIndex,
    });
    this.location.go(this.router.url.split('?')[0], url.searchParams.toString());
  }

  private reactToQueryParams() {
    this.activatedRoute.queryParams.pipe(takeUntil(this.onDestroy$)).subscribe(params => {
      const value = params[this.pageParamName];
      if (value != null) {
        this.setPage = +value;
      }
    });
  }

  setWrapperClasses(forced: boolean = false) {
    if (!this.tableWrapperClasses || forced) {
      this.tableWrapperClasses = 'mat-table-wrapper mat-elevation-z0 w-100';
      if (this.isResponsive) {
        this.tableWrapperClasses += ' responsive-table';
      }
      if (this.responsiveNow) {
        this.tableWrapperClasses += ' responsive-now';
      }
      if (!this.visible) {
        this.tableWrapperClasses += ' d-none';
      }

      if (forced) {
        this.cdr.markForCheck();
      }
    }
  }

  isArray(obj: any) {
    return Array.isArray(obj);
  }

  ngOnDestroy(): void {
    this.onDestroy$.next();
    this.onDestroy$.complete();
    this.stopDataSourceSubscription$.complete();
    this.resumeDataSourceSubscription$.complete();
  }

  /**
   * Run auto refresh when 'realtimeRefresh' values `true` and config `enabled`
   */
  private subscribeAutoRefresh() {
    if (this.realtimeRefresh) {
      this.autoRefreshService
        .getIntervalChanged()
        .pipe(takeUntil(this.onDestroy$))
        .subscribe({
          next: res => {
            if (res.enable) {
              if (this.searchRealtime) {
                this.dataSource.refresh(true);
              }
            }
          },
        });
    }
  }

  /**
   * Clear and set CSS classes string for each row's and store it in \
   * tableRowClasses
   */
  private fillTableRowClasses(rows: any) {
    if (rows && Array.isArray(rows)) {
      this.tableRowClasses = [];
      rows.forEach((row, index) => {
        this.tableRowClasses.push({
          index,
          classes: typeof this.rowClasses === 'function' ? this.rowClasses(index, row) : '',
        });
      });
    }
  }

  /**
   * Returns CSS classes for table cells
   * @param column TableColumn of the displayed row
   * @param row displayed row's data
   */
  getCellClasses(column: TableColumn, row: any, rowIndex: number, colIndex: number): string {
    let textClasses = `${column.align ? column.align : ''}`;

    if (column.getCellClasses != null) {
      return `${column.getCellClasses(row, rowIndex, colIndex)} ${textClasses}`;
    }
    return textClasses;
  }

  /**
   * Returns CSS classes for table's header (th)
   * @param column TableColumn of the displayed row
   */
  getHeaderClasses(column: TableColumn): string {
    let textClasses = `${column.align ? column.align : ''}`;
    return column.headerClasses ? `${column.headerClasses} ${textClasses}` : textClasses;
  }

  /**
   * Returns CSS classes for table's footer row
   * @param column TableColumn of the displayed row
   */
  getFooterClasses(column: TableColumn): string {
    let textClasses = `${column.align ? column.align : ''}`;
    return column.headerClasses ? `${column.headerClasses} ${textClasses}` : textClasses;
  }

  /**
   * Context menu handler for table rows
   */
  onRowContextMenu(event: MouseEvent, menu: TableRowMenuItem, row: any) {
    const contextMenuEnabled =
      typeof menu.hasContextMenu === 'function' ? menu.hasContextMenu(row) : menu.hasContextMenu;
    if (contextMenuEnabled) {
      const menuData = { type: 'ROW', row, menu };
      this.openContextMenu(event, menuData);
    }
  }

  /**
   * Context menu handler for button
   */
  onButtonContextMenu(event: MouseEvent, link: string) {
    const menuData = { type: 'BUTTON', link };
    this.openContextMenu(event, menuData);
  }

  /**
   * Helper function to open context menu and set data for it
   */
  private openContextMenu(event: MouseEvent, menuData: any) {
    event.preventDefault();
    this.contextMenuPosition.x = event.clientX + 'px';
    this.contextMenuPosition.y = event.clientY + 'px';
    this.contextMenu.menuData = menuData;
    this.contextMenu.menu?.focusFirstItem('mouse');
    this.contextMenu.openMenu();
  }

  /**
   * Click handler for "Open in new tab/window" context menu item on table row menus
   */
  openInNewTabOrWindowRowMenu(event: MouseEvent, menu: TableRowMenuItem, row: any, type: 'TAB' | 'WINDOW') {
    if (typeof menu.action === 'function') {
      menu.action(event, row, true, type === 'WINDOW' ? 'width=1280,height=700' : '');
    }
  }

  /**
   * Click handler for "Open in new tab/window" context menu item on button
   */
  openInNewTabOrWindowButton(event: MouseEvent, link: string, type: 'TAB' | 'WINDOW') {
    window.open(link, '_blank', type === 'WINDOW' ? 'width=1280,height=700' : '');
  }

  search(filter: SearchAttemptFields) {
    this.searchRealtime = false;
    this.filter = filter;
    this.paginator.pageIndex = 0; // always reset to first page upon new search attempt
    this.loadData();
    this.show();

    setTimeout(() => {
      this.searchRealtime = true;
    }, 3000);
  }

  reset() {
    this.searchRealtime = false;
    this.filter = null;
    this.paginator.pageIndex = 0;
    this.loadData();

    setTimeout(() => {
      this.searchRealtime = true;
    }, 3000);
  }

  hide() {
    this.reset();
    this.visible = false;
    this.setWrapperClasses(true);
  }

  show() {
    this.visible = true;
    this.setWrapperClasses(true);
  }

  detectChanges() {
    this.cdr.detectChanges();
  }
}

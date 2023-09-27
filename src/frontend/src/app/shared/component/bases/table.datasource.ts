import { CollectionViewer } from '@angular/cdk/collections';
import { DataSource } from '@angular/cdk/table';
import { Observable, Subject } from 'rxjs';

/**
 * Option interface on fetching data for table.
 */
export interface TableLoadOption {
  /**
   * The index of page to be loaded - can be null when pagination is not being used.
   */
  pageIndex?: number;

  /**
   * The max number of items per page - can be null when pagination is not being used.
   */
  pageSize?: number;

  /**
   * The search filter param
   */
  filter?: any;

  /**
   * The sort param
   */
  sort?: string;
}

/**
 * Base class for mat-table data source
 */
export abstract class TableDataSource<MODEL> implements DataSource<MODEL> {
  protected rowSubject$ = new Subject<MODEL[]>();
  protected footerSubject$ = new Subject<any>();
  protected countSubject$ = new Subject<number>();
  protected option!: TableLoadOption;

  /** a flag that can be use to indicate whether it is currently loading for data (from backend) or not */
  loading = false;

  connect(collectionViewer?: CollectionViewer): Observable<any[]> {
    return this.rowSubject$;
  }

  disconnect(collectionViewer: CollectionViewer): void {
    this.rowSubject$.complete();
    this.countSubject$.complete();
    this.footerSubject$.complete();
  }

  countObservable(): Observable<any> {
    return this.countSubject$;
  }

  footerObservable(): Observable<any> {
    return this.footerSubject$;
  }

  fetch(option: TableLoadOption): void | Promise<any> {
    this.option = option;
    return this.loadData(this.option);
  }

  refresh(loading: boolean = true): void | Promise<any> {
    this.loading = loading;
    return this.loadData(this.option);
  }

  startLoading() {
    this.loading = true;
  }

  stopLoading(withDelay: boolean = true) {
    const delay = withDelay == true ? 1000 : 0;
    setTimeout(() => {
      this.loading = false;
    }, delay);
  }

  /**
   * Each table datasource class will specify their own fetch operations.
   * @param option TableLoadOption
   */
  abstract loadData(option: TableLoadOption): void | Promise<any>;

  /**
   * Set if table uses pagination or not.
   */
  abstract get isPaginated(): boolean;

  get isUsingFooter(): boolean {
    return false;
  }
}

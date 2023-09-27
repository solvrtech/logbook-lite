export interface Response {
  success?: boolean;
  message?: string;
  data?: any;
}

export interface PaginateData<T> {
  /** response array of paginated data */
  items: T[];

  /** total number of data being paginated */
  totalItems: number;

  /** total number of resulted page(s) */
  totalPage: number;

  /** how many data on content */
  size: number;

  /** true if this is the first page */
  first: boolean;

  /** false if this is the last page */
  last: boolean;

  /** current page index number */
  page: number;
}

import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable, of, throwError } from 'rxjs';
import { catchError, map } from 'rxjs/operators';
import { environment } from 'src/environments/environment';
import { buildHttpParams } from '../../helpers/helpers.helper';
import { Response } from '../../interfaces/response.interface';
import { TableLoadOption } from './table.datasource';

const API_URL = environment.apiUrl;

/**
 * Base class for Angular Service class to do API call (get, post, put, delete)
 */
export class BaseApiService {
  /**
   * add parameter that array to query string. will generate paramValue=value1&paramValue=value2 ....
   * @param paramName name of field for param
   * @param values values for http
   * @param baseHttpParam base http parameter. if null will create new
   */
  static putArrayParam(paramName: string, values: Array<string> | null, baseHttpParam?: HttpParams): HttpParams {
    if (values && !Array.isArray(values as any)) {
      values = [values as any];
    }
    if (!values || values.length === 0) {
      return !!baseHttpParam ? baseHttpParam : new HttpParams();
    }
    if (baseHttpParam == null) {
      baseHttpParam = new HttpParams();
    }
    let current = baseHttpParam;
    for (const v of values) {
      current = current.append(paramName, v);
    }
    return current;
  }

  constructor(protected http: HttpClient) {}

  get<T>(url: string, option?: TableLoadOption | any, context?: any): Observable<T> {
    let params = {};
    if (option) {
      if (option instanceof HttpParams) {
        params = option;
      } else {
        params = this.buildTableGetParams(option);
      }
    }
    return this.getWorker(`${API_URL}/${url}`, params, context);
  }

  /**
   * invoker get with all ready param
   * @param finalUrl url include abse
   * @param params param for get
   */
  protected getWorker<T>(finalUrl: string, params: any, context?: any): Observable<T> {
    return this.http.get<Response>(finalUrl, { params, context: context }).pipe(
      map(
        res => {
          if (res && res.success) {
            return res?.data;
          }
          return throwError(() => res.message);
        },
        catchError(() => {
          return of(null);
        })
      )
    );
  }

  post(url: string, option?: any): Observable<any> {
    return this.http.post<Response>(`${API_URL}/${url}`, option).pipe(
      map(res => {
        if (res.success) {
          return res.data;
        }
        return throwError(() => res.message);
      }),
      catchError(() => {
        return of(null);
      })
    );
  }

  put(url: string, option?: any): Observable<any> {
    return this.http.put<Response>(`${API_URL}/${url}`, option).pipe(
      map(res => {
        if (res.success) {
          return res.data;
        }
        return throwError(() => res.message);
      }),
      catchError(() => {
        return of(null);
      })
    );
  }

  patch(url: string, body: any | null, options?: Object): Observable<any> {
    return this.http.patch<Response>(`${API_URL}/${url}`, body, options).pipe(
      map(res => {
        if (res.success) {
          return res.data;
        }
        return throwError(() => res.message);
      }),
      catchError(() => {
        return of(null);
      })
    );
  }

  delete(url: string): Observable<any> {
    return this.http.delete<Response>(`${API_URL}/${url}`).pipe(
      map(res => {
        if (res.success) {
          return res.data;
        }
        return throwError(() => res.message);
      }),
      catchError(() => {
        return of(null);
      })
    );
  }

  /**
   * Build HttpParams from TableLoadOption for tabular display with/without search
   * @param option TableLoadOption
   */
  protected buildTableGetParams(option: TableLoadOption): HttpParams {
    let params = new HttpParams();

    if (option.pageIndex) {
      params = params.set('page', option.pageIndex.toString());
    }

    if (option.pageSize) {
      params = params.set('size', option.pageSize.toString());
    }

    if (option.filter) {
      // we don't need this here (unlike ECR BO)
      // if (option.filter.hasOwnProperty('pattern')) {
      //   params = params.set('filter', option.filter.pattern);
      // }
      params = buildHttpParams(option.filter, params);
    }

    return params;
  }
}

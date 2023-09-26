import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';
import { BreadCrumb } from '../../interfaces/common.interface';

@Injectable({
  providedIn: 'root',
})
export class BreadcrumbService {
  breadCrumbs$ = new BehaviorSubject<BreadCrumb[]>([]);
  currentBreadCrumbs!: BreadCrumb[];

  /**
   * Sets value of array type to breadcrumb.
   *
   * @param {BreadCrumb} breadCrumbs
   * @memberof BreadcrumbService
   */
  async setBreadCrumb(breadCrumbs: BreadCrumb[]) {
    this.currentBreadCrumbs = breadCrumbs;
    this.breadCrumbs$.next(this.currentBreadCrumbs);
  }
}

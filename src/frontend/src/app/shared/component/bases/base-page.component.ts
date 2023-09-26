import { Injectable } from '@angular/core';
import { FormGroup } from '@angular/forms';
import { ActivatedRoute, Params, Router } from '@angular/router';
import { ViewMode } from '../../interfaces/common.interface';
import { BaseComponent } from './base.component';

export interface PageMode {
  viewMode: ViewMode;
  id?: string;
}

/**
 * Base class for page-kind of component. It provides permissions check upon component activation.
 */
@Injectable()
export abstract class BasePageComponent extends BaseComponent {
  /** query params of the previous page (ex: products index page) */
  queryParams?: Params;

  /**
   * change all control to enable/disable
   */
  changeFormControlEnable(formGroups: FormGroup, enable: boolean) {
    if (!formGroups) {
      return;
    }
    const keys = Object.keys(formGroups.controls);
    if (enable) {
      keys.forEach(key => {
        formGroups.controls[key].enable();
      });
    } else {
      keys.forEach(key => {
        formGroups.controls[key].disable();
      });
    }
  }

  /**
   * Get PageMode object based on the given activatedRoute.
   * Activated route should contain values for ":mode" and ":id" as route parameters, or else it will be navigated to
   * the returlUrl, which is usually a list or index page.
   *
   * @param activatedRoute ActivatedRoute instance on the current page
   * @param router Router instance
   * @param returnUrl string of the return URL (ex: to the index page)
   */
  getPageMode(activatedRoute: ActivatedRoute, router: Router, returnUrl: string): PageMode {
    const id = activatedRoute.snapshot.paramMap.get('id');
    let viewMode = activatedRoute.snapshot.paramMap.get('mode');

    if (id != null) {
      if (viewMode !== 'edit' && viewMode !== 'detail') {
        router.navigate([returnUrl]);
      }
    } else {
      if (activatedRoute.snapshot.url[activatedRoute.snapshot.url.length - 1].path === 'create') {
        viewMode = 'create';
      } else {
        router.navigate([returnUrl]);
      }
    }

    return {
      id: id ?? '',
      viewMode: viewMode as ViewMode,
    };
  }

  getEditDataValue(editData: { [key: string]: any }, key: string): any {
    return editData && editData[key] ? editData[key] : null;
  }

  /**
   * Get URL query params (which usually came from previous page) and stored them into `queryParams`.
   */
  initQueryParams(router: Router) {
    const state = router.getCurrentNavigation()?.extras.state;
    this.queryParams = { ...state };
  }
}

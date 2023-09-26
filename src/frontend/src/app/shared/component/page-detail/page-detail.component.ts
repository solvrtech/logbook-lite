import { Component, Input, TemplateRef } from '@angular/core';
import { Params } from '@angular/router';
import { BehaviorSubject } from 'rxjs';
import { AppLogo } from 'src/app/apps/interfaces/app.interface';
import { BreadCrumb, PageState } from '../../interfaces/common.interface';

@Component({
  selector: 'app-page-detail',
  templateUrl: './page-detail.component.html',
  styleUrls: ['./page-detail.component.scss'],
})
export class PageDetailComponent {
  // Can be used to set css hasNowrap
  @Input('hasNowrap') set hasNowrap(value: boolean) {
    this.hasNowrap$.next(value);
  }
  // The page's title
  @Input() title!: string;

  @Input() hasTitle!: boolean;

  @Input() breadCrumbs!: BreadCrumb[];

  @Input() appLogo?: AppLogo;

  // current page state
  @Input() state: PageState = 'loading';

  // Can be used to set error message
  @Input() message!: string;

  // URL target for the back button
  @Input() returnUrl?: string;

  // URL target for the back button
  @Input() queryParams?: Params;

  // Use this to render the main content from parent component
  @Input() bodyTemplate!: TemplateRef<any>;

  // Use this to render additional buttons from parent component
  @Input() buttonsTemplate!: TemplateRef<any>;

  // custom CSS classes for top wrapper container
  @Input() pageClasses!: string;

  // custom CSS classes for grid columns
  @Input() gridClasses: string = 'inner-card col-12';

  // custom CSS classes for mat-card wrapper
  @Input() wrapperClasses!: string;

  // <app-page> will be used if this is false, otherwise a plain <div> (easier if this component is used as dialog)
  @Input() asDialog: boolean = false;

  @Input() dataTitle!: string;

  /** Observable for css hasRightSection */
  hasNowrap$ = new BehaviorSubject<boolean>(false);

  // custom CSS classes for panel columns
  @Input() panelClasses!: string;

  // Use this to render the panel content from parent component
  @Input() panelTemplate!: TemplateRef<any>;
}

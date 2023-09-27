import { Component, Input, TemplateRef } from '@angular/core';
import { Params } from '@angular/router';
import { BehaviorSubject } from 'rxjs';
import { AppLogo } from 'src/app/apps/interfaces/app.interface';
import { BreadCrumb, PageState } from '../../interfaces/common.interface';

@Component({
  selector: 'app-page',
  templateUrl: './page.component.html',
  styleUrls: ['./page.component.scss'],
})
export class PageComponent {
  // Can be used to set title
  @Input('title') set title(value: string) {
    this.title$.next(value || '');
  }

  // Can be used to set css hasRightSection
  @Input('hasRightSection') set hasRightSection(value: boolean) {
    this.hasRightSection$.next(value);
  }

  @Input('hasTitle') set hasTitle(value: boolean) {
    this.hasTitle$.next(value);
  }

  // App logo for app detail page

  @Input() appLogo?: AppLogo;

  // Can be used to set breadCrumbs
  @Input() breadCrumbs!: BreadCrumb[];

  // current page state
  @Input() state: PageState = 'loading';

  // Can be used to set error message
  @Input() message!: string;

  // URL target for the back button
  @Input() returnUrl?: string;

  // URL target for the back button
  @Input() queryParams?: Params;

  get title() {
    return this.title$.getValue() ?? '';
  }

  /** Observable for title */
  title$ = new BehaviorSubject<any>('');

  /** Observable for css hasRightSection */
  hasRightSection$ = new BehaviorSubject<boolean>(false);

  hasTitle$ = new BehaviorSubject<boolean>(false);

  // Can be used to set title with data
  @Input() dataTitle!: string;

  // Use this to render the main content from parent component
  @Input() bodyTemplate!: TemplateRef<any>;
}

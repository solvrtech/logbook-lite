import { Injectable } from '@angular/core';
import { Title } from '@angular/platform-browser';
import { ActivatedRoute, UrlSegment } from '@angular/router';
import { marker as _ } from '@biesbjerg/ngx-translate-extract-marker';
import { TranslateService } from '@ngx-translate/core';
import { BehaviorSubject, Observable, lastValueFrom } from 'rxjs';

@Injectable({
  providedIn: 'root',
})
export class TitleService {
  title$ = new BehaviorSubject('');
  hasRightSection$ = new BehaviorSubject<boolean>(false);
  hasNowrap$ = new BehaviorSubject<boolean>(false);
  hasTitle$ = new BehaviorSubject<boolean>(true);
  url$: Observable<UrlSegment[]>;
  currentTitle = '';
  currentTitleToken = '';

  constructor(private route: ActivatedRoute, private title: Title, private translateService: TranslateService) {
    this.url$ = this.route.url;
  }

  /**
   * Sets value of any type to title.
   *
   * @param {*} value
   * @memberof TitleService
   */
  async setTitle(value: any) {
    this.currentTitleToken = value.toString();

    if (this.currentTitleToken !== '') {
      const titleString = await lastValueFrom(this.translateService.get(this.currentTitleToken));
      const titleCommon = await lastValueFrom(this.translateService.get(_('app.title')));

      this.currentTitle = titleCommon;
      if (titleString) {
        this.currentTitle = `${titleString} / ${this.currentTitle}`;
      }

      this.title$.next(titleString);
      this.title.setTitle(this.currentTitle);
    }
  }

  refreshCurrentTitle() {
    this.setTitle(this.currentTitleToken);
  }
}

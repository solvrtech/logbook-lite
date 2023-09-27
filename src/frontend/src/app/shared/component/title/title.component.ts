import { Component, Input } from '@angular/core';
import { TitleService } from '../../services/title/title.service';

@Component({
  selector: 'app-title',
  template: '',
})
export class TitleComponent {
  @Input() set title(value: string | null) {
    this.titleService.setTitle(value);
  }

  @Input() set hasTitle(value: boolean | null) {
    this.titleService.hasTitle$.next(!!value);
  }

  @Input() set hasRightSection(value: boolean | null) {
    this.titleService.hasRightSection$.next(!!value);
  }

  @Input() set hasNowrap(value: boolean | null) {
    this.titleService.hasNowrap$.next(!!value);
  }

  constructor(private titleService: TitleService) {}
}

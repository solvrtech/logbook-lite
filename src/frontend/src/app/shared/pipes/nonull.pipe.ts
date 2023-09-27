import { Injectable, Pipe, PipeTransform } from '@angular/core';
import { marker as _ } from '@biesbjerg/ngx-translate-extract-marker';
import { TranslateService } from '@ngx-translate/core';

@Pipe({
  name: 'nonull',
  pure: false,
})
@Injectable()
export class NoNullPipe implements PipeTransform {
  constructor(private translateService: TranslateService) {}

  transform(value: any): any {
    if (Number.isFinite(value)) {
      return value;
    } else if (typeof value === 'boolean') {
      return this.translateService.instant(value ? _('common.active') : _('common.not_active'));
    } else if (typeof value !== 'undefined' && value) {
      return this.translateService.instant(value);
    } else {
      return this.translateService.instant(_('empty.value'));
    }
  }
}

import { Injectable, Pipe, PipeTransform } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { formatNumberDefault } from '../helpers/helpers.helper';

@Pipe({
  name: 'formatNumberDefault',
  pure: false,
})
@Injectable()
export class FormatNumberPipe implements PipeTransform {
  constructor(private translateService: TranslateService) {}

  transform(value: number): any {
    if (typeof value === 'undefined') return null;
    return formatNumberDefault(value, this.translateService.currentLang);
  }
}

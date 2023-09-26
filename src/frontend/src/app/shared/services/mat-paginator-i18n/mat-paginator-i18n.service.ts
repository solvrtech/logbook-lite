import { Injectable } from '@angular/core';
import { MatPaginatorIntl } from '@angular/material/paginator';
import { TranslateService } from '@ngx-translate/core';

@Injectable()
export class MatPaginatorI18nService extends MatPaginatorIntl {
  constructor(private translateService: TranslateService) {
    super();

    this.translateService.onLangChange.subscribe((e: Event) => {
      this.translateMatPaginator();
    });

    this.translateMatPaginator();
  }

  public override getRangeLabel = (page: number, pageSize: number, length: number): string => {
    if (length === 0 || pageSize === 0) {
      return `0 / ${length}`;
    }

    length = Math.max(length, 0);

    const startIndex: number = page * pageSize;
    const endIndex: number = startIndex < length ? Math.min(startIndex + pageSize, length) : startIndex + pageSize;

    return this.translateService.instant('paginator.get_range_label', {
      start: startIndex + 1,
      end: endIndex,
      length,
    });
  };

  public translateMatPaginator(): void {
    this.translateService.get('paginator.item_per_pages').subscribe((translation: any) => {
      this.itemsPerPageLabel = translation;
      this.changes.next();
    });
  }
}

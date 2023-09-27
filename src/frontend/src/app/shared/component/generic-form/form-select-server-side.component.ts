import { Component, EventEmitter, Input, OnDestroy, OnInit, Output } from '@angular/core';
import { FormControl } from '@angular/forms';
import { ReplaySubject } from 'rxjs';
import { debounceTime, delay, filter, map, takeUntil, tap } from 'rxjs/operators';
import { FormDropdown } from '../../interfaces/common.interface';
import { BaseFormFieldComponent } from './form-field.base';

@Component({
  selector: 'app-form-select-server-side',
  templateUrl: './form-select-server-side.component.html',
})
export class FormSelectServerSideComponent extends BaseFormFieldComponent implements OnInit, OnDestroy {
  /** Initial array of options which can be filtered later on */
  @Input() options: FormDropdown[] = [];

  @Input() description!: string;

  /** Emitted on filter change */
  @Output() filterChange = new EventEmitter<string>();

  /** FormControl for ngx-mat-select-search */
  searchFormControl: FormControl = new FormControl();

  /** indicate search operation is in progress */
  searching: boolean = false;

  /** The displayed options on the dropdown */
  filteredOptions$: ReplaySubject<FormDropdown[]> = new ReplaySubject<FormDropdown[]>(1);

  constructor() {
    super();
  }

  ngOnInit(): void {
    if (this.label.length == 0) {
      this.fieldClasses = 'no-label';
    }

    if (this.description != null) {
      let search = this.description;
      this.filteredOptions$.next(this.options.filter(options => options.description.indexOf(search) > -1));
    }

    // listen for search field value changes
    this.searchFormControl.valueChanges
      .pipe(
        filter(search => !!search),
        tap(() => (this.searching = true)),
        takeUntil(this.onDestroy$),
        debounceTime(200),
        map(search => {
          if (!this.options) {
            return [];
          }

          // simulate server fetching and filtering data
          return this.options.filter(options => options.description.toLowerCase().indexOf(search.toLowerCase()) > -1);
        }),
        delay(500)
      )
      .subscribe({
        next: filterOptions => {
          this.searching = false;
          this.filteredOptions$.next(filterOptions);
        },
        error: () => {
          // no errors in our simulated example
          this.searching = false;
          // handle error...
        },
      });
  }
}

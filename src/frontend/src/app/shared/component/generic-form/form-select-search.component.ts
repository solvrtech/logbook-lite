import {
  ChangeDetectorRef,
  Component,
  EventEmitter,
  Input,
  OnChanges,
  OnInit,
  Output,
  SimpleChanges,
} from '@angular/core';
import { FormControl } from '@angular/forms';
import { MatSelectChange } from '@angular/material/select';
import { ReplaySubject } from 'rxjs';
import { debounceTime, filter, takeUntil, tap } from 'rxjs/operators';
import { FormDropdown } from '../../interfaces/common.interface';
import { BaseFormFieldComponent } from './form-field.base';

/**
 * Generic wrapper component for searchable (or non-searchable) mat-select using reactive form driven technique.
 */
@Component({
  selector: 'app-form-select-search',
  templateUrl: './form-select-search.component.html',
})
export class FormSelectSearchComponent extends BaseFormFieldComponent implements OnInit, OnChanges {
  /** Set true to make a searchable dropdown, or false for a standard dropdown */
  @Input() useSearch = true;

  /** Set true to use server-side searching */
  @Input() useServerSideSearch = false;

  /** Initial array of options which can be filtered later on */
  @Input() options: FormDropdown[] = [];

  /** Emitted on selection change event */
  @Output() selectionChange = new EventEmitter<MatSelectChange>();

  /** Emitted on filter change */
  @Output() filterChange = new EventEmitter<string>();

  /** The displayed options on the dropdown */
  filteredOptions$ = new ReplaySubject<FormDropdown[]>(1);

  /** FormControl for ngx-mat-select-search */
  searchFormControl = new FormControl();

  /** indicate search operation is in progress */
  searching = false;

  @Input() link!: string;
  @Input() clickLink = false;
  @Input() dialog = false;

  @Output() info = new EventEmitter<any>();

  onClick(event: any) {
    this.info.emit(event);
  }

  constructor(private cd: ChangeDetectorRef) {
    super();
  }

  ngOnInit() {
    if (this.label.length == 0) {
      this.fieldClasses = 'no-label';
    }
    this.control.valueChanges.pipe(takeUntil(this.onDestroy$)).subscribe((value: any) => this.valueChanged.emit(value));
    this.valueChanged.emit(this.control.value);
    // listen for value changes on FormControl that ngx-mat-select-search uses
    this.searchFormControl.valueChanges
      .pipe(
        filter(search => !!search),
        tap(search => {
          if (search && search.length >= 3) {
            this.searching = true;
          }
        }),
        takeUntil(this.onDestroy$),
        debounceTime(200)
      )
      .subscribe({
        next: () => {
          this.searching = false;
          if (!this.useServerSideSearch) {
            this.filterOptions();
          }
          this.filterChange.emit(this.searchFormControl.value);
        },
        error: () => {
          this.searching = false;
        },
      });
  }

  ngOnChanges(changes: SimpleChanges) {
    if (changes['options']) {
      setTimeout(() => {
        this.filteredOptions$.next(this.options);
        this.cd.detectChanges();
      });
    }
  }

  getDropdownWithIcon(value: string): FormDropdown | undefined {
    return value ? this.options.find(val => val.value === value) : this.options[0];
  }

  /**
   * Filter options and assign it to filteredOptions
   */
  protected filterOptions() {
    if (this.options) {
      // build the search parameters
      let search = this.searchFormControl.value;
      if (!search) {
        this.filteredOptions$.next(this.options.slice());
        return;
      } else {
        search = search.toLowerCase();
      }

      // filter options data
      this.filteredOptions$.next(this.options.filter(option => option.description.toLowerCase().indexOf(search) > -1));
    }
  }
}

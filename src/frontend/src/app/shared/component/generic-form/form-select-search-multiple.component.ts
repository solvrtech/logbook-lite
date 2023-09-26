import {
  AfterViewInit,
  ChangeDetectorRef,
  Component,
  EventEmitter,
  Input,
  OnChanges,
  OnInit,
  Output,
  SimpleChanges,
  ViewChild,
} from '@angular/core';
import { FormControl } from '@angular/forms';
import { MatSelect, MatSelectChange } from '@angular/material/select';
import { ReplaySubject } from 'rxjs';
import { take, takeUntil } from 'rxjs/operators';
import { FormDropdown } from '../../interfaces/common.interface';
import { BaseFormFieldComponent } from './form-field.base';

@Component({
  selector: 'app-form-select-search-multiple',
  templateUrl: './form-select-search-multiple.component.html',
})
export class FormSelectSearchMultipleComponent
  extends BaseFormFieldComponent
  implements OnInit, AfterViewInit, OnChanges
{
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

  @ViewChild('multiSelect') multiSelect!: MatSelect;

  constructor(private cd: ChangeDetectorRef) {
    super();
  }

  ngOnInit(): void {
    this.control.valueChanges.pipe(takeUntil(this.onDestroy$)).subscribe((value: any) => this.valueChanged.emit(value));
    this.valueChanged.emit(this.control.value);

    this.filteredOptions$.next(this.options.slice());

    this.searchFormControl.valueChanges.pipe(takeUntil(this.onDestroy$)).subscribe(() => {
      this.filterMulti();
    });
  }

  ngOnChanges(changes: SimpleChanges): void {
    if (changes['options']) {
      setTimeout(() => {
        this.filteredOptions$.next(this.options);
        this.cd.detectChanges();
      });
    }
  }

  ngAfterViewInit() {
    this.setInitialValue();
  }

  protected setInitialValue() {
    this.filteredOptions$.pipe(take(1), takeUntil(this.onDestroy$)).subscribe(() => {
      this.multiSelect.compareWith = (a: FormDropdown, b: FormDropdown) => a && b && a.value === b.value;
    });
  }

  protected filterMulti() {
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

import { ENTER } from '@angular/cdk/keycodes';
import { Component, Input, OnInit } from '@angular/core';
import { FormBuilder, FormControl, FormGroup } from '@angular/forms';
import { MatChipInputEvent } from '@angular/material/chips';
import { marker as _ } from '@biesbjerg/ngx-translate-extract-marker';
import { LogService } from 'src/app/logs/services/logs/log.service';
import { BaseComponent } from 'src/app/shared/component/bases/base.component';
import { AlertsService } from 'src/app/shared/services/alerts/alerts.service';

@Component({
  selector: 'app-tags',
  templateUrl: './tags.component.html',
  styleUrls: ['./tags.component.scss'],
})
export class TagsComponent extends BaseComponent implements OnInit {
  //if set, this will override the default permission for this form field
  @Input() hasPermission!: boolean;

  // if set, this will be used to update tag for this component (and `edit` will be true)
  @Input() idLog!: string;

  // if set, this will override the default tags data model
  @Input() tags: any[] = [];

  tag: any[] = [];
  formGroup!: FormGroup;
  editMode: boolean = false;
  isLoading: boolean = false;
  readonly separatorKeysCodes = [ENTER] as const;

  constructor(private fb: FormBuilder, private logService: LogService, private alert: AlertsService) {
    super();
  }

  ngOnInit(): void {
    this.initFromGroup();
  }

  /**
   * Setup form controls and initial validators
   *
   * @private
   */
  private initFromGroup() {
    this.formGroup = this.fb.group({
      tags: new FormControl({ value: '', disabled: !this.hasPermission }),
    });
  }

  /**
   * Delete tag
   *
   * @param {string} tag$
   */
  removeKeyword(tag$: string) {
    const index = this.tags.indexOf(tag$);

    if (index >= 0) {
      this.tags.splice(index, 1);
      this.save(this.tags);
    }
  }

  /**
   * Create new a tag
   *
   * @param {MatChipInputEvent} event
   */
  onAddTag(event: MatChipInputEvent): void {
    const value = (event.value || '').trim();

    if (this.tags != null) {
      const data = this.tags.filter((item: any) => item == value.toLowerCase());
      if (value && data.length <= 0) {
        this.tags.push(value.toLowerCase());
        this.save(this.tags);
      } else {
        this.alert.setErrorWithData(_('app.tags_duplicate'), value);
      }
    } else {
      if (value) {
        this.tag.push(value.toLowerCase());
        this.save(this.tag);
      }
    }

    event.chipInput!.clear();
  }

  /**
   * Saves and updates for tags
   *
   * @param {any} tags
   */
  private save(tags: any) {
    if (this.formGroup.valid) {
      this.isLoading = true;

      this.logService.updateTags(this.idLog, tags).subscribe({
        next: res => {
          if (res && res.success) {
            setTimeout(() => {
              this.tags = res.data.tag ? res.data.tag : '';
              this.isLoading = false;
              this.editMode = false;
            }, 1000);
          } else {
            this.isLoading = true;
            this.editMode = true;
          }
        },
        error: err => {
          this.isLoading = true;
          this.editMode = true;
        },
      });
    }
  }
}

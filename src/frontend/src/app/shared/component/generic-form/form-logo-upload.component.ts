import { Component, EventEmitter, Input, OnInit, Output, ViewChild } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { marker as _ } from '@biesbjerg/ngx-translate-extract-marker';
import { TranslateService } from '@ngx-translate/core';
import * as filesize from 'filesize';
import { takeUntil } from 'rxjs/operators';
import { AlertsService } from 'src/app/shared/services/alerts/alerts.service';
import { environment } from 'src/environments/environment';
import { ImageCropperDialogComponent } from '../image-cropper-dialog/image-cropper-dialog.component';
import { ImageCropperDialogConfig } from '../image-cropper-dialog/image-cropper.interface';
import { BaseFormFieldComponent } from './form-field.base';

/**
 * Generic wrapper component for image uploader input using reactive form driven technique.
 * It can be used through ValidatedFieldComponent or as a stand alone component.
 */
@Component({
  selector: 'app-form-logo-upload',
  templateUrl: './form-logo-upload.component.html',
})
export class FormLogoUploadComponent extends BaseFormFieldComponent implements OnInit {
  /** MIME type filter for file <input> */
  @Input() acceptedMimeType: string = environment.imageCropper.mimeType;

  /** Max allowed file size in bytes */
  @Input() maxSize: number = environment.appLogo.maxFileSize;

  /** base64 string format of the current image */
  @Input() srcImage!: string;

  @Input() displayLogo!: string;

  /** Emits result of processed image by ImageCropperDialogComponent (or `currentImage`) */
  @Output() imageProcessed = new EventEmitter<string>();

  /** Bind the hidden file input that is used to open select file dialog */
  @ViewChild('file', { static: true }) file: any;

  constructor(
    private dialog: MatDialog,
    private alertService: AlertsService,
    private translateService: TranslateService
  ) {
    super();
  }

  ngOnInit() {}

  selectFile() {
    this.file.nativeElement.click();
    //select same image
    this.file.nativeElement.value = null;
  }

  fileChangeEvent(event: any): void {
    if (event) {
      const file = this.file.nativeElement.files[0];

      // check for mime type
      if (this.acceptedMimeType && this.acceptedMimeType.split(',').findIndex(type => type === file.type) === -1) {
        this.alertService.setError(_('images.upload.file_unsupported'));
        return;
      }

      // check for file size
      if (file.size > this.maxSize) {
        this.alertService.setError(
          this.translateService.instant(_('images.upload.file_size_exceeds'), { max: filesize(this.maxSize) })
        );
        return;
      }

      this.dialog
        .open(ImageCropperDialogComponent, {
          minWidth: '280px',
          width: '40vw',
          data: {
            event,
          } as ImageCropperDialogConfig,
        })
        .afterClosed()
        .pipe(takeUntil(this.onDestroy$))
        .subscribe(res => {
          if (res) {
            this.srcImage = res;
            this.imageProcessed.emit(this.srcImage);
          }
        });
    }
  }
}

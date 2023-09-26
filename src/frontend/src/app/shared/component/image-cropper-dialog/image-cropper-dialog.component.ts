import { Component, Inject, OnInit, ViewChild } from '@angular/core';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { ImageCroppedEvent, ImageTransform } from 'ngx-image-cropper';
import { environment } from 'src/environments/environment';
import { ImageCropperDialogConfig } from './image-cropper.interface';

@Component({
  selector: 'app-image-cropper-dialog',
  templateUrl: './image-cropper-dialog.component.html',
  styleUrls: ['./image-cropper-dialog.component.scss'],
})
export class ImageCropperDialogComponent implements OnInit {
  accept: string = environment.imageCropper.mimeType;
  /** Bind the hidden file input that is used to open select file dialog */
  @ViewChild('file', { static: true }) file: any;

  /** The change event from your file input (set to null to reset the cropper) */
  imageChangedEvent: any;

  /** The width / height image ratio (e.g. 1 / 1 for a square cropped result, 4 / 3, 16 / 9, etc) */
  aspectRatio: any;

  /** The cropper cannot be made smaller than this number of pixels in width (relative to original image's size) */
  cropperMinWidth!: number;

  /** Cropped image will be resized to at most this width (in px) */
  resizeToWidth!: number;

  /** Output format (png) */
  format: any;

  /** Keep track on cropped image result (base64) */
  croppedImage: string = '';

  /** True when image loading is failed */
  failedImageLoading = false;

  /** Show image cropper when this is true */
  showCropper = false;

  /** Current image transformation state */
  transform: ImageTransform = {};

  /** Current image scaling point */
  scale = 1;

  /** Keep track on dialog data */
  data!: ImageCropperDialogConfig;

  constructor(
    @Inject(MAT_DIALOG_DATA) data: ImageCropperDialogConfig,
    private dialogRef: MatDialogRef<ImageCropperDialogComponent>
  ) {
    if (data) {
      this.data = data;
      this.aspectRatio = data.aspectRatio ? this.data.aspectRatio : environment.imageCropper.aspectRatio;
      this.cropperMinWidth = data.cropperMinWidth ? data.cropperMinWidth : environment.imageCropper.minWidth;
      this.resizeToWidth = data.resizeToWidth ? data.resizeToWidth : environment.imageCropper.minWidth;
      this.format = data.format ? data.format : environment.imageCropper.resultFormat;
      this.imageChangedEvent = data.event;
    }
  }

  ngOnInit() {
    this.failedImageLoading = false;
  }

  imageCropped(event: ImageCroppedEvent) {
    this.croppedImage = event.base64!;
  }

  imageLoaded() {
    this.showCropper = true;
  }

  loadImageFailed() {
    this.failedImageLoading = true;
    this.showCropper = false;
  }

  onConfirm() {
    this.dialogRef.close(this.croppedImage);
  }
}

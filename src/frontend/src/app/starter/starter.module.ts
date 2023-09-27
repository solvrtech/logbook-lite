import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { MatBadgeModule } from '@angular/material/badge';
import { MatButtonModule } from '@angular/material/button';
import { MatButtonToggleModule } from '@angular/material/button-toggle';
import { MatDialogModule } from '@angular/material/dialog';
import { MatExpansionModule } from '@angular/material/expansion';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatListModule } from '@angular/material/list';
import { MatMenuModule } from '@angular/material/menu';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSelectModule } from '@angular/material/select';
import { MatSidenavModule } from '@angular/material/sidenav';
import { MatToolbarModule } from '@angular/material/toolbar';
import { MatTooltipModule } from '@angular/material/tooltip';
import { MatTreeModule } from '@angular/material/tree';
import { LoadingBarHttpClientModule } from '@ngx-loading-bar/http-client';
import { TranslateModule } from '@ngx-translate/core';
import { SharedModule } from '../shared/shared.module';
import { AutoRefreshDialogComponent } from './component/dialog/auto-refresh-dialog/auto-refresh-dialog.component';
import { DialogMessageComponent } from './component/dialog/dialog-message/dialog-message.component';
import { LeftSidebarComponent } from './component/left-sidebar/left-sidebar.component';
import { MessageComponent } from './component/message/message.component';
import { NavbarComponent } from './component/navbar/navbar.component';
import { StarterComponent } from './pages/starter/starter.component';
import { OrderByPipe } from './pipes/order-by.pipe';
import { StarterRoutingModule } from './starter-routing.module';
import { BannerComponent } from './component/banner/banner.component';

@NgModule({
  declarations: [
    StarterComponent,
    NavbarComponent,
    LeftSidebarComponent,
    OrderByPipe,
    AutoRefreshDialogComponent,
    DialogMessageComponent,
    MessageComponent,
    BannerComponent,
  ],
  imports: [
    CommonModule,
    StarterRoutingModule,
    MatToolbarModule,
    MatIconModule,
    SharedModule.forRoot(),
    MatButtonModule,
    MatButtonToggleModule,
    MatTooltipModule,
    MatMenuModule,
    MatTreeModule,
    MatSidenavModule,
    MatFormFieldModule,
    MatSelectModule,
    MatListModule,
    TranslateModule,
    MatDialogModule,
    ReactiveFormsModule,
    MatProgressSpinnerModule,
    LoadingBarHttpClientModule,
    MatBadgeModule,
    MatExpansionModule,
  ],
})
export class StarterModule {}

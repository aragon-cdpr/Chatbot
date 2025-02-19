import { IonicModule } from '@ionic/angular';
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HomePageComponent } from './home-page-component';

import { HomePageRoutingModule } from './home-page-routing.module';
import { ExploreContainerComponentModule } from 'src/app/components/explore-container/explore-container.module';
import { StackedCardComponentModule } from 'src/app/components/stacked-card/stacked-card.module';
import { CustomMatCardComponentModule } from 'src/app/components/custom-mat-card/custom-mat-card.module';

@NgModule({
  imports: [
    IonicModule,
    CommonModule,
    FormsModule,
    ExploreContainerComponentModule,
    StackedCardComponentModule,
    CustomMatCardComponentModule,
    HomePageRoutingModule
  ],
  declarations: [HomePageComponent]
})
export class HomePageModule {}

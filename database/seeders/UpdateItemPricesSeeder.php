<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateItemPricesSeeder extends Seeder
{
    /**
     * Run the database seeds to update item prices.
     */
    public function run(): void
    {
        Log::info('Starting to update item prices');
        
        // Define price data for each item by name
        $itemPrices = [
            '2x4 Lumber' => ['cost' => 2.50, 'price' => 3.75],
            'Drywall Sheet' => ['cost' => 12.00, 'price' => 18.00],
            'Paint' => ['cost' => 25.00, 'price' => 35.00],
            '16d Nails' => ['cost' => 5.50, 'price' => 8.25],
            'Plywood 3/4"' => ['cost' => 32.00, 'price' => 45.00],
            'R-13 Insulation' => ['cost' => 15.00, 'price' => 22.50],
            'Romex 14/2' => ['cost' => 0.45, 'price' => 0.65],
            'Electrical Box' => ['cost' => 2.25, 'price' => 3.50],
            'PVC Pipe 1"' => ['cost' => 3.75, 'price' => 5.50],
            'Joint Compound' => ['cost' => 14.50, 'price' => 21.75],
        ];
        
        // Default prices for items not in the list
        $defaultCost = 10.00;
        $defaultPrice = 15.00;
        
        try {
            DB::beginTransaction();
            
            // Get all items
            $items = Item::all();
            
            foreach ($items as $item) {
                // Check if we have specific prices for this item
                if (isset($itemPrices[$item->name])) {
                    $cost = $itemPrices[$item->name]['cost'];
                    $price = $itemPrices[$item->name]['price'];
                } else {
                    // Use default prices
                    $cost = $defaultCost;
                    $price = $defaultPrice;
                }
                
                // Update the item
                $item->update([
                    'cost' => $cost,
                    'price' => $price,
                ]);
                
                Log::info("Updated item prices", [
                    'id' => $item->id,
                    'name' => $item->name,
                    'cost' => $cost,
                    'price' => $price
                ]);
            }
            
            DB::commit();
            Log::info('Successfully updated all item prices');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating item prices', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
} 
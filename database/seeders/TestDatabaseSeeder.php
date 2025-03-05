// Create some estimates
if (Schema::hasTable('estimates')) {
    foreach ($tenants as $tenant) {
        for ($i = 1; $i <= 5; $i++) {
            $estimate = Estimate::create([
                'tenant_id' => $tenant->id,
                'customer_name' => 'Customer ' . $i,
                'customer_email' => 'customer' . $i . '@example.com',
                'customer_phone' => '555-' . str_pad($i, 3, '0', STR_PAD_LEFT) . '-1234',
                'customer_address' => $i . ' Main St, Anytown, USA',
                'status' => ['draft', 'sent', 'approved', 'declined'][array_rand(['draft', 'sent', 'approved', 'declined'])],
                'markup_percentage' => rand(0, 20),
                'discount_percentage' => rand(0, 10),
                'notes' => 'Sample estimate ' . $i . ' for tenant ' . $tenant->id,
                'valid_until' => now()->addDays(30),
                'version' => 1,
            ]);

            // Add some items to the estimate
            $items = Item::where('tenant_id', $tenant->id)->take(3)->get();
            foreach ($items as $item) {
                EstimateItem::create([
                    'tenant_id' => $tenant->id,
                    'estimate_id' => $estimate->id,
                    'item_id' => $item->id,
                    'original_item_id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'unit_of_measure' => $item->unit_of_measure,
                    'quantity' => rand(1, 10),
                    'material_cost_rate' => $item->material_cost_rate,
                    'material_charge_rate' => $item->material_charge_rate,
                    'labor_units' => $item->labor_units,
                    'original_cost_rate' => $item->material_cost_rate,
                    'original_charge_rate' => $item->material_charge_rate,
                ]);
            }
        }
    }
} 
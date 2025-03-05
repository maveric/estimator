<?php

namespace App\Livewire\Estimates;

use App\Models\Estimate;
use App\Models\EstimateVersion;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Log;

#[Layout('layouts.app')]
class EstimateView extends Component
{
    public $estimate;
    public $estimateId;
    public $showVersionHistory = false;
    public $selectedVersion = null;
    
    // Calculated totals
    public $totalCost = 0;
    public $totalCharge = 0;
    public $subtotalCost = 0;
    public $subtotalCharge = 0;
    public $markupAmount = 0;
    public $discountAmount = 0;
    
    public function mount(Estimate $estimate)
    {
        $this->estimate = $estimate;
        $this->estimateId = $estimate->id;
        $this->calculateTotals();
    }
    
    public function loadEstimate()
    {
        try {
            $this->estimate = Estimate::with([
                'items.item', 
                'assemblies.assembly', 
                'assemblies.items.item', 
                'packages.assemblies.items.item',
                'versions'
            ])->findOrFail($this->estimateId);
                
            $this->calculateTotals();
        } catch (\Exception $e) {
            Log::error('Error loading estimate', [
                'id' => $this->estimateId,
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Error loading estimate: ' . $e->getMessage());
        }
    }
    
    public function calculateTotals()
    {
        $totals = $this->estimate->calculateTotals();
        
        $this->subtotalCost = $totals['subtotal_cost'];
        $this->subtotalCharge = $totals['subtotal_charge'];
        $this->markupAmount = $totals['markup_amount'];
        $this->discountAmount = $totals['discount_amount'];
        $this->totalCost = $totals['total_cost'];
        $this->totalCharge = $totals['total_charge'];
    }
    
    public function toggleVersionHistory()
    {
        $this->showVersionHistory = !$this->showVersionHistory;
        
        if ($this->showVersionHistory && !$this->estimate->relationLoaded('versions')) {
            $this->estimate->load('versions');
        }
    }
    
    public function viewVersion($versionId)
    {
        try {
            $this->selectedVersion = EstimateVersion::findOrFail($versionId);
        } catch (\Exception $e) {
            Log::error('Error loading estimate version', [
                'id' => $versionId,
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Error loading estimate version: ' . $e->getMessage());
        }
    }
    
    public function closeVersionView()
    {
        $this->selectedVersion = null;
    }
    
    public function revertToVersion($versionId)
    {
        try {
            $version = EstimateVersion::findOrFail($versionId);
            $snapshot = $version->snapshot;
            
            // Update the estimate with the snapshot data
            $this->estimate->update([
                'customer_name' => $snapshot['estimate']['customer_name'],
                'customer_email' => $snapshot['estimate']['customer_email'],
                'customer_phone' => $snapshot['estimate']['customer_phone'],
                'customer_address' => $snapshot['estimate']['customer_address'],
                'status' => $snapshot['estimate']['status'],
                'markup_percentage' => $snapshot['estimate']['markup_percentage'],
                'discount_percentage' => $snapshot['estimate']['discount_percentage'],
                'notes' => $snapshot['estimate']['notes'],
                'valid_until' => $snapshot['estimate']['valid_until'],
            ]);
            
            // Create a new version snapshot before making changes
            $this->estimate->createVersionSnapshot();
            
            // Reload the estimate
            $this->loadEstimate();
            
            $this->selectedVersion = null;
            
            session()->flash('message', 'Estimate reverted to version ' . $version->version_number . ' successfully.');
        } catch (\Exception $e) {
            Log::error('Error reverting to estimate version', [
                'id' => $versionId,
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Error reverting to estimate version: ' . $e->getMessage());
        }
    }
    
    public function changeStatus($status)
    {
        try {
            $this->estimate->update(['status' => $status]);
            
            session()->flash('message', 'Estimate status updated successfully.');
            
            // Reload the estimate
            $this->loadEstimate();
        } catch (\Exception $e) {
            Log::error('Error updating estimate status', [
                'id' => $this->estimate->id,
                'status' => $status,
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Error updating estimate status: ' . $e->getMessage());
        }
    }
    
    public function convertToInvoice()
    {
        // This will be implemented in a future update
        session()->flash('message', 'Convert to invoice functionality will be implemented in a future update.');
    }
    
    public function exportPdf()
    {
        // This will be implemented in a future update
        session()->flash('message', 'PDF export functionality will be implemented in a future update.');
    }
    
    public function exportCsv()
    {
        // This will be implemented in a future update
        session()->flash('message', 'CSV export functionality will be implemented in a future update.');
    }
    
    public function render()
    {
        return view('livewire.estimates.view');
    }
} 
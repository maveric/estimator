<?php

namespace App\Livewire\Estimates;

use App\Models\Estimate;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Log;

#[Layout('layouts.app')]
class EstimateList extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $dateFilter = '';
    
    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'dateFilter' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingDateFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Estimate::query()
            ->where('tenant_id', auth()->user()->current_tenant_id);
        
        // Apply search filter if provided
        if ($this->search) {
            $query->where(function($q) {
                $q->where('customer_name', 'like', '%' . $this->search . '%')
                  ->orWhere('customer_email', 'like', '%' . $this->search . '%')
                  ->orWhere('customer_phone', 'like', '%' . $this->search . '%')
                  ->orWhere('id', 'like', '%' . $this->search . '%');
            });
        }
        
        // Apply status filter if provided
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }
        
        // Apply date filter if provided
        if ($this->dateFilter) {
            switch ($this->dateFilter) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'this_week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereMonth('created_at', now()->month)
                          ->whereYear('created_at', now()->year);
                    break;
                case 'this_year':
                    $query->whereYear('created_at', now()->year);
                    break;
            }
        }
        
        // Get estimates with eager loading
        $estimates = $query->with(['items', 'assemblies.items'])
                          ->orderBy('created_at', 'desc')
                          ->paginate(10);
        
        // Calculate totals for each estimate
        foreach ($estimates as $estimate) {
            $totals = $estimate->calculateTotals();
            $estimate->total_cost = $totals['total_cost'];
            $estimate->total_charge = $totals['total_charge'];
        }
        
        return view('livewire.estimates.list', [
            'estimates' => $estimates
        ]);
    }

    public function deleteEstimate($id)
    {
        try {
            $estimate = Estimate::findOrFail($id);
            $estimate->delete();
            
            session()->flash('message', 'Estimate deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting estimate', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Error deleting estimate: ' . $e->getMessage());
        }
    }

    public function changeStatus($id, $status)
    {
        try {
            $estimate = Estimate::findOrFail($id);
            $estimate->update(['status' => $status]);
            
            session()->flash('message', 'Estimate status updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating estimate status', [
                'id' => $id,
                'status' => $status,
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Error updating estimate status: ' . $e->getMessage());
        }
    }
} 
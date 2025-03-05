# CRUD Implementation Plan

## 1. Base Structure

### 1.1 Controllers
- `ItemController`
- `AssemblyController`
- `PackageController`
- `LaborRateController`
- `EstimateController`

### 1.2 Livewire Components
```php
app/Livewire/
├── Items/
│   ├── ItemList.php
│   ├── ItemForm.php      # Handles both create/edit
│   └── ItemSelect.php
├── Assemblies/
│   ├── AssemblyList.php
│   ├── AssemblyForm.php  # Handles both create/edit
│   └── AssemblyItemManager.php
├── Packages/
│   ├── PackageList.php
│   ├── PackageForm.php   # Handles both create/edit
│   └── PackageAssemblyManager.php
└── LaborRates/
    ├── LaborRateList.php
    └── LaborRateForm.php # Handles both create/edit
```

### 1.3 Views Structure
```
resources/views/livewire/
├── items/
│   ├── list.blade.php
│   ├── form.blade.php    # Shared create/edit template
│   └── select.blade.php
├── assemblies/
│   ├── list.blade.php
│   ├── form.blade.php    # Shared create/edit template
│   └── item-manager.blade.php
├── packages/
│   ├── list.blade.php
│   ├── form.blade.php    # Shared create/edit template
│   └── assembly-manager.blade.php
└── labor-rates/
    ├── list.blade.php
    └── form.blade.php    # Shared create/edit template
```

## 2. Implementation Priorities

### 2.1 Phase 1: Base Items
- Basic CRUD for Items
- Labor Rate management
- Input validation
- Tenant isolation

### 2.2 Phase 2: Assemblies
- Assembly management
- Item-to-Assembly relationships
- Quantity management
- Cost calculations

### 2.3 Phase 3: Packages
- Package management
- Assembly-to-Package relationships
- Package templates
- Total cost calculations

### 2.4 Phase 4: Estimates
- Estimate creation/editing
- Direct item addition
- Package/Assembly integration
- Cost summaries and reporting

## 3. Key Features Per Entity

### 3.1 Items
- Basic info (name, description, SKU)
- Unit of measure
- Cost rates (material, labor)
- Labor units
- Active/inactive status

### 3.2 Assemblies
- Name and description
- Item management
- Quantity per item
- Total cost calculations
- Template functionality

### 3.3 Packages
- Name and description
- Assembly management
- Default quantities
- Total cost calculations
- Template functionality

### 3.4 Labor Rates
- Rate name
- Cost and charge rates
- Effective dates
- Active/inactive status

## 4. Security Considerations

### 4.1 Tenant Isolation
- Middleware for tenant scoping
- Relationship checks
- Query constraints

### 4.2 User Permissions
- Role-based access control
- Action-based permissions
- Data visibility rules

## 5. UI/UX Considerations

### 5.1 List Views
- Sortable columns
- Filters
- Search functionality
- Pagination
- Bulk actions

### 5.2 Forms
- Shared form components for create/edit operations
- Mode prop to determine create vs edit behavior
- Real-time validation
- Dynamic calculations
- Nested form handling
- Auto-save functionality
- Reusable form sections for common fields

### 5.3 Relationships
- Dynamic item selection
- Quantity adjustments
- Cost previews
- Drag-and-drop ordering

## 6. Testing Strategy

### 6.1 Unit Tests
- Model relationships
- Calculations
- Validation rules

### 6.2 Feature Tests
- CRUD operations
- Tenant isolation
- Permission checks
- Form submissions

### 6.3 Integration Tests
- End-to-end workflows
- Complex calculations
- Data consistency

## 7. Implementation Notes

### 7.1 Copy Logic
- Deep copy for estimate creation
- Relationship maintenance
- History tracking
- Version control preparation

### 7.2 Calculation Logic
- Real-time updates
- Caching strategy
- Aggregate calculations
- Rounding rules

### 7.3 Validation Rules
- Required fields
- Format constraints
- Business logic rules
- Cross-field validations

## 8. Future Considerations

- Audit logging
- Version control
- Bulk operations
- Import/Export functionality
- API endpoints
- Reporting features

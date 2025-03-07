# Estimate Module Refactor Design Document

## Overview

This document outlines the plan and design principles for tearing down and rebuilding the estimates module. The goal is to create a modular, maintainable, and scalable architecture that emphasizes code reuse and consistency across all data types. The same item object type should be used whether it appears directly on an estimate or as part of an assembly.

## Goals

- **Tear Down the Existing Code:**  
  Remove the current monolithic structure of the estimates module, which has become disjointed and difficult to maintain.

- **Rebuild with a Clear, Modular Architecture:**  
  Rebuild the estimates module using discrete, reusable Livewire components that encapsulate specific responsibilities.

- **Focus on Code Reuse & Consistency:**  
  Ensure that logic and views for items, assemblies, and packages are reused wherever possible, avoiding duplication. An item should have a consistent representation across different contexts (standalone or within an assembly).

- **Minimize Duplication:**  
  Extract common logic and presentation details into shared components and services to keep the code DRY (Don't Repeat Yourself).

## Design Principles

- **Separation of Concerns:**  
  Each component should have a single responsibility.  
  - **ItemComponent:** Handles the display and minimal logic for an individual item.
  - **AssemblyComponent:** Manages a collection of items, using ItemComponent for rendering.
  - **PackageComponent:** Manages a collection of assemblies, using AssemblyComponent.
  - **EstimateForm Component:** Acts as the parent, maintaining overall estimate state and coordinating nested updates.

- **Component Reusability:**  
  Shared Blade components and Livewire components should be used to standardize UI elements and interactions, ensuring consistent behavior and appearance.

- **Event-Driven Communication:**  
  Use Livewire’s event system to allow each component to handle its own state changes and pass summary data upward.
  - Each component performs its own calculations (e.g., updating totals) and only emits necessary summary data.
  - This keeps the EstimateForm component focused on the overall state and minimizes unnecessary data traffic.

- **Server-Side Efficiency:**  
  All event emissions and listeners are processed on the server during the same AJAX request cycle, ensuring real-time updates without additional network round trips.

## Proposed Architecture

### Folder & File Structure

app/
└── Livewire/
  └── Estimates/
    ├── EstimateForm.php # Main estimate form component
    ├── PackageComponent.php # Handles a single package
    ├── AssemblyComponent.php # Handles a single assembly
    └── ItemComponent.php # Handles a single item

resources/
└── views/
  └── livewire/
    └── estimates/
      ├── form.blade.php # Main estimate form view
      ├── package.blade.php # View for package component
      ├── assembly.blade.php # View for assembly component
      └── item.blade.php # View for item component


### Component Responsibilities

1. **EstimateForm Component**  
   - Manages the overall state of the estimate.
   - Coordinates nested components (packages, assemblies, items).
   - Handles final form submission and overall validations.

2. **PackageComponent**  
   - Manages a single package.
   - Contains a collection of assemblies.
   - Emits summarized package data (e.g., total values) upward.

3. **AssemblyComponent**  
   - Manages a single assembly.
   - Contains a collection of items.
   - Calculates and emits its total or changes to the parent component.

4. **ItemComponent**  
   - Handles an individual item.
   - Provides a consistent view and logic whether used directly in an estimate or as part of an assembly.
   - Emits events (like value changes) upward to trigger re-calculations.

### Data Flow & Event Emission

- **Event Bubbling:**  
  Use `$this->emitUp()` in each component to bubble events up one level. Each parent component listens, processes its part of the data, and then re-emits a summary to its parent.

- **Encapsulated Calculations:**  
  Each component handles its own calculations so that by the time an event reaches the EstimateForm component, it only deals with summarized totals or high-level state information.

## Implementation Steps

1. **Plan the Data Model:**  
   - Define the expected data structures for items, assemblies, and packages.
   - Document the data contracts for each component.

2. **Refactor Incrementally:**  
   - Start with the smallest unit (ItemComponent).  
   - Build the AssemblyComponent to use the ItemComponent.
   - Build the PackageComponent to use the AssemblyComponent.
   - Integrate these into the EstimateForm component.

3. **Extract Shared Logic & Components:**  
   - Identify common UI patterns (e.g., input fields, error messages) and extract them into reusable Blade components.
   - Extract shared logic into services or traits where applicable.

4. **Implement Communication:**  
   - Set up event listeners and emissions at each component level.
   - Ensure that events are processed on the server side to provide real-time updates.

5. **Test & Validate:**  
   - Write tests for each component to ensure that state updates and event communications work as expected.
   - Ensure that the overall estimate state reflects the aggregated results from nested components.

## Conclusion

This refactor aims to rebuild the estimates module from the ground up with a focus on modularity, code reuse, and a clear separation of responsibilities. Each component (Item, Assembly, Package, and EstimateForm) will manage its own logic and communicate via events, ensuring a consistent, maintainable, and scalable codebase.

---

This document serves as a reference throughout the refactor process to ensure that design principles and architectural goals are adhered to. Happy coding and refactoring!

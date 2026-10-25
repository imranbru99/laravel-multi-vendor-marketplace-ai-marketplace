# Module development

`App\Domain\Modules\Module` defines the contract. `RegistryModule` adapts entries from config/marketplace.php. `ModuleRegistry` validates unknown dependencies and cycles. `ModuleManager` combines platform controls, plan entitlements, tenant state, vendor/branch restrictions and role denials, then checks dependencies recursively. Scoped overrides can restrict but cannot bypass a disabled parent. Registry metadata includes version, installed state, scope and configuration-schema placeholders. Arbitrary module configuration forms remain planned.

Core catalog cannot be disabled. Authentication, tenant isolation, order history, audit and accounting integrity are not toggleable. New operations call require/transaction. Existing financial obligations remain serviceable from history resources. Digital downloads deliberately remain available to entitled purchasers even if new digital selling is disabled.

Add a module by defining its key/name/category/dependencies, migration/model/service, explicit authorization, UI/actions, tests and demo content. `marketplace:sync-modules` adds new keys disabled. Administrators enable prerequisites before dependents. Tests must cover disabled routes and concurrent shutdown where transactions are created.

Do not register placeholder navigation or advertise planned capabilities as available. Form schemas and resource columns are registered in config/resources.php; workflow actions belong to services, not generic status editors. Financial/stock event histories have no edit/delete actions.

<script setup lang="ts">
//#region Imports
import { onMounted, ref, watch, computed } from "vue";
import AlertPlaceholder from "../../base-components/AlertPlaceholder";
import DataList from "../../base-components/DataList";
import LoadingOverlay from "../../base-components/LoadingOverlay";
import { useI18n } from "vue-i18n";
import Button from "../../base-components/Button";
import Lucide from "../../base-components/Lucide";
import Table from "../../base-components/Table";
import { TitleLayout, TwoColumnsLayout } from "../../base-components/Form/FormLayout";
import { FormInput, FormInputCode, FormLabel, FormTextarea, FormSelect, FormSwitch } from "../../base-components/Form";
import { ViewMode } from "../../types/enums/ViewMode";
import EmployeeService from "../../services/EmployeeService";
import { Employee } from "../../types/models/Employee";
import { EmployeeFormFieldValues } from "../../types/requests/EmployeeFormFieldValues";
import { Collection } from "../../types/resources/Collection";
import { ServiceResponse } from "../../types/services/ServiceResponse";
import { Resource } from "../../types/resources/Resource";
import { DataListEmittedData } from "../../base-components/DataList/DataList.vue";
import { Dialog } from "../../base-components/Headless";
import { TwoColumnsLayoutCards } from "../../base-components/Form/FormLayout/TwoColumnsLayout.vue";
import { DropDownOption } from "../../types/models/DropDownOption";
import { EmployeeFormRequest } from "../../types/requests/EmployeeFormRequest";
import DashboardService from "../../services/DashboardService";
import CacheService from "../../services/CacheService";
import { debounce } from "lodash";
import { CardState } from "../../types/enums/CardState";
import { SearchRequest } from "../../types/requests/SearchRequest";
import { FormActions } from "vee-validate";
import { useSelectedUserLocationStore } from "../../stores/user-location";
//#endregion

//#region Interfaces
//#endregion

//#region Declarations
const { t } = useI18n();
const cacheServices = new CacheService();
const dashboardServices = new DashboardService();
const selectedUserStore = useSelectedUserLocationStore();
const userLocation = computed(() => selectedUserStore.selectedUserLocation);
const employeeServices = new EmployeeService();
//#endregion

//#region Data - Pinia
//#endregion

//#region Data - UI
const mode = ref<ViewMode>(ViewMode.LIST);
const loading = ref<boolean>(false);
  const datalistErrors = ref<Record<string, string> | null>(null);
const cards = ref<Array<TwoColumnsLayoutCards>>([
  { title: 'Company Information', state: CardState.Expanded, },
  { title: 'Employee Information', state: CardState.Expanded, },
  { title: '', state: CardState.Hidden, id: 'button' }
]);
const deleteUlid = ref<string>("");
const deleteModalShow = ref<boolean>(false);
const expandDetail = ref<number | null>(null);
//#endregion

//#region Data - Views
const employeeForm = ref<EmployeeFormRequest>({
  data: {
    id: '',
    ulid: '',
    company: {
      id: '',
      ulid: '',
      code: '',
      name: '',
      address: '',
      default: false,
      status: ''
    },
    user: {
      id: '',
      ulid: '',
      name: '',
      email: '',
      email_verified: false,
      profile: {
        first_name: '',
        last_name: '',
        address: '',
        city: '',
        postal_code: '',
        country: '',
        status: 'ACTIVE',
        tax_id: 0,
        ic_num: 0,
        img_path: '',
        remarks: '',
      },
      roles: [],
      companies: [],
      settings: {
        theme: 'side-menu-light-full',
        date_format: 'yyyy_MM_dd',
        time_format: 'hh_mm_ss',
      }
    },
    employee_accesses: {
      id: '',
      ulid: '',
      company: {
        id: '',
        ulid: '',
        code: '',
        name: '',
        address: '',
        default: false,
        status: ''
      },
      code: '',
      name: '',
      address: '',
      city: '',
      contact: '',
      is_main: false,
      remarks: '',
      status: 'ACTIVE',    
    },
    code: '',
    selected_companies: '',
    selected_accesses: '',
    join_date: '',
    status: 'ACTIVE',
  }
});

const employeeLists = ref<Collection<Employee[]> | null>({
  data: [],
  meta: {
    current_page: 0,
    from: null,
    last_page: 0,
    path: '',
    per_page: 0,
    to: null,
    total: 0,
  },
  links: {
    first: '',
    last: '',
    prev: null,
    next: null,
  }
});
const countriesDDL = ref<Array<DropDownOption> | null>(null);
const statusDDL = ref<Array<DropDownOption> | null>(null);
//#endregion

//#region onMounted
onMounted(async () => {
  await getEmployees('', true, true, 1, 10);

  getDDL();
});
//#endregion

//#region Methods
const getEmployees = async (search: string, refresh: boolean, paginate: boolean, page: number, per_page: number) => {  
  loading.value = true;

  let company_id = userLocation.value.company.id;  

  const searchReq: SearchRequest = {
    search: search,
    refresh: refresh,
    paginate: paginate,
    page: page,
    per_page: per_page
  };

  let result: ServiceResponse<Collection<Array<Employee>> | Resource<Array<Employee>> | null> = await employeeServices.readAny(company_id, searchReq);

  if (result.success && result.data) {
    employeeLists.value = result.data as Collection<Employee[]>;
  } else {
    datalistErrors.value = result.errors as Record<string, string>;
  }

  loading.value = false;
}

const getDDL = (): void => {
  dashboardServices.getCountriesDDL().then((result: Array<DropDownOption> | null) => {
    statusDDL.value = result;
  });
  dashboardServices.getStatusDDL().then((result: Array<DropDownOption> | null) => {
    statusDDL.value = result;
  });
}

const selectedCompanyId = () => {
  return userLocation.value.company.id;
}

const emptyEmployee = () => {
  return {
    data: {
      id: '',
      ulid: '',
      company: {
        id: '',
        ulid: '',
        code: '',
        name: '',
        address: '',
        default: false,
        status: ''
      },
      user: {
        id: '',
        ulid: '',
        name: '',
        email: '',
        email_verified: false,
        profile: {
          first_name: '',
          last_name: '',
          address: '',
          city: '',
          postal_code: '',
          country: '',
          status: 'ACTIVE',
          tax_id: 0,
          ic_num: 0,
          img_path: '',
          remarks: '',
        },
        roles: [],
        companies: [],
        settings: {
          theme: 'side-menu-light-full',
          date_format: 'yyyy_MM_dd',
          time_format: 'hh_mm_ss',
        }
      },
      employee_accesses: {
        id: '',
        ulid: '',
        company: {
          id: '',
          ulid: '',
          code: '',
          name: '',
          address: '',
          default: false,
          status: ''
        },
        code: '',
        name: '',
        address: '',
        city: '',
        contact: '',
        is_main: false,
        remarks: '',
        status: 'ACTIVE',    
      },
      code: '',
      selected_companies: '',
      selected_accesses: '',
      join_date: '',
      status: 'ACTIVE',
    }
  }
}

const onDataListChanged = (data: DataListEmittedData) => {
  getEmployees(data.search.text, false, true, data.pagination.page, data.pagination.per_page);
}

const createNew = () => {
  mode.value = ViewMode.FORM_CREATE;

  let cachedData: unknown | null = cacheServices.getLastEntity('Employee');

  employeeForm.value = cachedData == null ? emptyEmployee() : cachedData as EmployeeFormRequest;
}

const viewSelected = (idx: number) => {
  if (expandDetail.value === idx) {
    expandDetail.value = null;
  } else {
    expandDetail.value = idx;
  }
};

const editSelected = (itemIdx: number) => {
  mode.value = ViewMode.FORM_EDIT;
  employeeForm.value.data = employeeLists.value?.data[itemIdx] as Employee;
}

const deleteSelected = (itemUlid: string) => {
  deleteUlid.value = itemUlid;
  deleteModalShow.value = true;
}

const confirmDelete = async () => {
  deleteModalShow.value = false;
  loading.value = true;

  let result: ServiceResponse<boolean | null> = await employeeServices.delete(deleteUlid.value);

  if (result.success) {
    backToList();
  } else {
    console.log(result);
  }

  loading.value = true;
}

const handleExpandCard = (index: number) => {
  if (cards.value[index].state === CardState.Collapsed) {
    cards.value[index].state = CardState.Expanded
  } else if (cards.value[index].state === CardState.Expanded) {
    cards.value[index].state = CardState.Collapsed
  }
}

const onSubmit = async (values: EmployeeFormFieldValues, actions: FormActions<EmployeeFormFieldValues>) => {
  loading.value = true;

  let result: ServiceResponse<Employee | null> = {
    success: false,
  }

  if (mode.value == ViewMode.FORM_CREATE) {
    result = await employeeServices.create(values);
  } else if (mode.value == ViewMode.FORM_EDIT) {
    let employee_ulid = employeeForm.value.data.ulid;

    result = await employeeServices.update( employee_ulid, values);
  } else {
    result.success = false;
  }

  if (!result.success) {
    actions.setErrors({ code: 'error' });
  } else {
    backToList();
  }

  loading.value = false;
};

const backToList = async () => {
  loading.value = true;

  cacheServices.removeLastEntity('Employee');

  mode.value = ViewMode.LIST;
  await getEmployees('', true, true, 1, 10);

  loading.value = false;
}
//#endregion

//#region Computed
const titleView = computed(() => {
  switch (mode.value) {
    case ViewMode.FORM_CREATE:
      return t('views.employee.actions.create');
    case ViewMode.FORM_EDIT:
      return t('views.employee.actions.edit');
    case ViewMode.LIST:
    default:
      return t('views.employee.page_title');
  }
});
//#endregion

//#region Watcher
watch(
  employeeForm,
  debounce((newValue): void => {
    if (mode.value != ViewMode.FORM_CREATE) return;
    cacheServices.setLastEntity('Employee', newValue)
  }, 500),
  { deep: true }
);
//#endregion
</script>

<template>
  <div class="mt-8">
    <LoadingOverlay :visible="loading">
      <TitleLayout>
        <template #title>
          {{ titleView }}
        </template>
        <template #optional>
          <div class="flex w-full mt-4 sm:w-auto sm:mt-0">
            <Button v-if="mode == ViewMode.LIST" as="a" href="#" variant="primary" class="shadow-md" @click="createNew">
              <Lucide icon="Plus" class="w-4 h-4" />&nbsp;{{
                t("components.buttons.create_new")
              }}
            </Button>
          </div>
        </template>
      </TitleLayout>

      <div v-if="mode == ViewMode.LIST">
        <AlertPlaceholder :errors="datalistErrors" :title="t('views.employee.table.title')" />
        <DataList :title="t('views.employee.table.title')" :enable-search="true" :can-print="true" :can-export="true"
          :pagination="employeeLists ? employeeLists.meta : null" @dataListChanged="onDataListChanged">
          <template #content>
            <Table class="mt-5" :hover="true">
              <Table.Thead variant="light">
                <Table.Tr>
                  <Table.Th class="whitespace-nowrap">
                    {{ t("views.employee.table.cols.code") }}
                  </Table.Th>
                  <Table.Th class="whitespace-nowrap">
                    {{ t("views.employee.table.cols.name") }}
                  </Table.Th>
                  <Table.Th class="whitespace-nowrap">
                    {{ t("views.employee.table.cols.is_main") }}
                  </Table.Th>
                  <Table.Th class="whitespace-nowrap">
                    {{ t("views.employee.table.cols.status") }}
                  </Table.Th>
                  <Table.Th class="whitespace-nowrap"></Table.Th>
                </Table.Tr>
              </Table.Thead>
              <Table.Tbody v-if="employeeLists !== null">
                <template v-if="employeeLists.data.length == 0">
                  <Table.Tr class="intro-x">
                    <Table.Td colspan="5">
                      <div class="flex justify-center italic">{{ t('components.data-list.data_not_found') }}</div>
                    </Table.Td>
                  </Table.Tr>
                </template>
                <template v-for="( item, itemIdx ) in employeeLists.data" :key="item.ulid">
                  <Table.Tr class="intro-x">
                    <Table.Td>{{ item.code }}</Table.Td>
                    <Table.Td>{{ item.join_date }}</Table.Td>
                    <Table.Td>
                      <Lucide v-if="item.status === 'ACTIVE'" icon="CheckCircle" />
                      <Lucide v-if="item.status === 'INACTIVE'" icon="X" />
                    </Table.Td>
                    <Table.Td>
                      <div class="flex justify-end gap-1">
                        <Button variant="outline-secondary" @click="viewSelected(itemIdx)">
                          <Lucide icon="Info" class="w-4 h-4" />
                        </Button>
                        <Button variant="outline-secondary" @click="editSelected(itemIdx)">
                          <Lucide icon="CheckSquare" class="w-4 h-4" />
                        </Button>
                        <Button variant="outline-secondary" @click="deleteSelected(item.ulid)">
                          <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                        </Button>
                      </div>
                    </Table.Td>
                  </Table.Tr>
                  <Table.Tr :class="{ 'intro-x': true, 'hidden transition-all': expandDetail !== itemIdx }">
                    <Table.Td colspan="5">
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.employee.fields.code') }}</div>
                        <div class="flex-1">{{ item.code }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.user.fields.name') }}</div>
                        <div class="flex-1">{{ item.user.name }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.user.fields.email') }}</div>
                        <div class="flex-1">{{ item.user.email }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.profile.fields.address') }}</div>
                        <div class="flex-1">{{ item.user.profile.address }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.profile.fields.city') }}</div>
                        <div class="flex-1">{{ item.user.profile.city }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.profile.fields.postal_code') }}</div>
                        <div class="flex-1">{{ item.user.profile.postal_code }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.profile.fields.img_path') }}</div>
                        <div class="flex-1">{{ item.user.profile.img_path }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.profile.fields.country') }}</div>
                        <div class="flex-1">{{ item.user.profile.country }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.profile.fields.tax_id') }}</div>
                        <div class="flex-1">{{ item.user.profile.tax_id }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.profile.fields.ic_num') }}</div>
                        <div class="flex-1">{{ item.user.profile.ic_num }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.employee.fields.join_date') }}</div>
                        <div class="flex-1">{{ item.join_date }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.employee.fields.remarks') }}</div>
                        <div class="flex-1">{{ item.user.profile.remarks }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.employee.fields.status') }}</div>
                        <div class="flex-1">
                          <span v-if="item.status === 'ACTIVE'">
                            {{ t('components.dropdown.values.statusDDL.active') }}
                          </span>
                          <span v-if="item.status === 'INACTIVE'">
                            {{ t('components.dropdown.values.statusDDL.inactive') }}
                          </span>
                        </div>
                      </div>
                    </Table.Td>
                  </Table.Tr>
                </template>
              </Table.Tbody>
            </Table>
            <Dialog :open="deleteModalShow" @close="() => { deleteModalShow = false; }">
              <Dialog.Panel>
                <div class="p-5 text-center">
                  <Lucide icon="XCircle" class="w-16 h-16 mx-auto mt-3 text-danger" />
                  <div class="mt-5 text-3xl">{{ t('components.delete-modal.title') }}</div>
                  <div class="mt-2 text-slate-500">
                    {{ t('components.delete-modal.desc_1') }}
                    <br />
                    {{ t('components.delete-modal.desc_2') }}
                  </div>
                </div>
                <div class="px-5 pb-8 text-center">
                  <Button type="button" variant="outline-secondary" class="w-24 mr-1" @click="() => { deleteModalShow = false; }">
                    {{ t('components.buttons.cancel') }}
                  </Button>
                  <Button type="button" variant="danger" class="w-24" @click="(confirmDelete)">
                    {{ t('components.buttons.delete') }}
                  </Button>
                </div>
              </Dialog.Panel>
            </Dialog>
          </template>
        </DataList>
      </div>
      <div v-else>
        <VeeForm id="employeeForm" v-slot="{ errors }" @submit="onSubmit">
          <AlertPlaceholder :errors="errors" />
          <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
            <template #card-items-0>
              <div class="p-5">
                <div class="pb-4">
                  <label for="code" class="block bold font-semibold">{{ t('views.company.fields.name') }}</label>
                  <div class="flex-1">{{ userLocation.company.name }}</div>
                </div>
              </div>
            </template>
            <template #card-items-1>
              <div class="p-5">
                <VeeField v-slot="{ field }" :value=selectedCompanyId() name="company_id">                  
                  <FormInput id="company_id" name="company_id" type="hidden" v-bind="field" />
                </VeeField>                
                <div class="pb-4">
                  <FormLabel html-for="code" :class="{ 'text-danger': errors['code'] }">
                    {{ t('views.employee.fields.code') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" v-model="employeeForm.data.code" name="code" rules="required|alpha_dash"
                    :label="t('views.employee.fields.code')">
                    <FormInputCode id="code" v-bind="field" name="code" type="text" :class="{ 'border-danger': errors['code'] }" 
                      :placeholder="t('views.employee.fields.code')" />
                  </VeeField>
                  <VeeErrorMessage name="code" class="mt-2 text-danger" />
                </div>
                <div class="pb-4">
                  <FormLabel html-for="name" :class="{ 'text-danger': errors['name'] }">
                    {{ t('views.user.fields.name') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" v-model="employeeForm.data.user.name" name="name" rules="required|alpha_num"
                    :label="t('views.user.fields.name')">
                    <FormInput id="name" name="name" v-bind="field" type="text"
                      :class="{ 'border-danger': errors['name'] }" :placeholder="t('views.user.fields.name')" />
                  </VeeField>
                  <VeeErrorMessage name="name" class="mt-2 text-danger" />
                </div>
                <div class="pb-4">
                  <FormLabel html-for="email" :class="{ 'text-danger': errors['email'] }">
                    {{ t('views.user.fields.email') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" v-model="employeeForm.data.user.email" name="email" rules="required|email"
                    :label="t('views.user.fields.email')">
                    <FormInput id="email" name="email" v-bind="field" type="text"
                      :class="{ 'border-danger': errors['email'] }" :placeholder="t('views.user.fields.email')"
                      :readonly="mode === ViewMode.FORM_EDIT" />
                  </VeeField>
                  <VeeErrorMessage name="email" class="mt-2 text-danger" />
                </div>
                <div class="pb-4">
                  <FormLabel html-for="address" :class="{ 'text-danger': errors['address'] }">
                    {{ t('views.employee.fields.address') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" v-model="employeeForm.data.user.profile.address" name="address"
                    :label="t('views.employee.fields.address')">
                    <FormTextarea id="address" v-bind="field" name="address" type="text"
                      :class="{ 'border-danger': errors['address'] }" :placeholder="t('views.employee.fields.address')" />
                  </VeeField>
                </div>
                <div class="pb-4">
                  <FormLabel html-for="city" :class="{ 'text-danger': errors['city'] }">
                    {{ t('views.employee.fields.city') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" v-model="employeeForm.data.user.profile.city" name="city"
                    :label="t('views.employee.fields.city')">
                    <FormInput id="city" v-bind="field" name="city" type="text"
                      :class="{ 'border-danger': errors['city'] }" :placeholder="t('views.employee.fields.city')" />
                  </VeeField>
                </div>
                <div class="pb-4">
                  <FormLabel html-for="postal_code" :class="{ 'text-danger': errors['postal_code'] }">
                    {{ t('views.employee.fields.postal_code') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" v-model="employeeForm.data.user.profile.postal_code" name="postal_code"
                    :label="t('views.employee.fields.postal_code')">
                    <FormInput id="postal_code" v-bind="field" name="postal_code" type="text"
                      :class="{ 'border-danger': errors['postal_code'] }" :placeholder="t('views.employee.fields.contact')" />
                  </VeeField>
                </div>
                <div class="pb-4">
                  <FormLabel html-for="img_path" :class="{ 'text-danger': errors['img_path'] }">
                    {{ t('views.user.fields.picture') }}
                  </FormLabel>
                  <FormFileUpload id="img_path" v-model="employeeForm.data.user.profile.img_path" name="img_path" type="text"
                    :class="{ 'border-danger': errors['img_path'] }" :placeholder="t('views.user.fields.picture')" />
                </div>
                <div class="pb-4">
                  <FormLabel html-for="country" :class="{ 'text-danger': errors['country'] }">
                    {{ t('views.user.fields.country') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" v-model="employeeForm.data.user.profile.country" name="country" rules="required"
                    :label="t('views.user.fields.country')">
                    <FormSelect id="country" name="country" v-bind="field" :class="{ 'border-danger': errors['country'] }"
                      :placeholder="t('views.user.fields.country')">
                      <option value="">{{ t('components.dropdown.placeholder') }}</option>
                      <option v-for="c in countriesDDL" :key="c.name" :value="c.name">{{ c.name }}</option>
                    </FormSelect>
                  </VeeField>
                  <VeeErrorMessage name="country" class="mt-2 text-danger" />
                </div>
                <div class="pb-4">
                  <FormLabel html-for="tax_id" :class="{ 'text-danger': errors['tax_id'] }">
                    {{ t('views.user.fields.tax_id') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" v-model="employeeForm.data.user.profile.tax_id" name="tax_id" rules="required"
                    :placeholder="t('views.user.fields.tax_id')" :label="t('views.user.fields.tax_id')">
                    <FormInput id="tax_id" name="tax_id" v-bind="field" type="text"
                      :class="{ 'border-danger': errors['tax_id'] }" />
                  </VeeField>
                  <VeeErrorMessage name="tax_id" class="mt-2 text-danger" />
                </div>
                <div class="pb-4">
                  <FormLabel html-for="join_date">
                    {{ t('views.user.fields.settings.join_date') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" v-model="employeeForm.data.join_date" name="join_date">
                    <FormSelect v-show="mode == ViewMode.FORM_CREATE || mode == ViewMode.FORM_EDIT" id="join_date"
                      name="join_date" v-bind="field">
                      <option value="yyyy_MM_dd">{{ 'YYYY-MM-DD' }}</option>
                      <option value="dd_MMM_yyyy">{{ 'DD-MMM-YYYY' }}</option>
                    </FormSelect>
                  </VeeField>
                </div>
                <div class="pb-4">
                  <FormLabel html-for="remarks" :class="{ 'text-danger': errors['remarks'] }">
                    {{ t('views.employee.fields.remarks') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" v-model="employeeForm.data.user.profile.remarks" name="remarks" :label="t('views.employee.fields.remarks')">
                    <FormTextarea id="remarks" v-bind="field" name="remarks" type="text"
                      :class="{ 'border-danger': errors['remarks'] }" :placeholder="t('views.employee.fields.remarks')" rows="3" />
                  </VeeField>
                </div>
                <div class="pb-4">
                  <FormLabel html-for="status" :class="{ 'text-danger': errors['status'] }">
                    {{ t('views.employee.fields.status') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" v-model="employeeForm.data.status" name="status" rules="required" :label="t('views.employee.fields.status')">
                    <FormSelect id="status" v-bind="field" name="status"
                      :class="{ 'border-danger': errors['status'] }">
                      <option value="">{{ t('components.dropdown.placeholder') }}</option>
                      <option v-for="c in statusDDL" :key="c.code" :value="c.code">{{ t(c.name) }}</option>
                    </FormSelect>
                  </VeeField>
                  <VeeErrorMessage name="status" class="mt-2 text-danger" />
                </div>
              </div>
            </template>
            <template #card-items-button>
              <div class="flex gap-4">
                <Button type="submit" href="#" variant="primary" class="w-28 shadow-md">
                  {{ t("components.buttons.submit") }}
                </Button>
                <Button type="button" href="#" variant="soft-secondary" class="w-28 shadow-md">
                  {{ t("components.buttons.reset") }}
                </Button>
              </div>
            </template>
          </TwoColumnsLayout>
        </VeeForm>
        <Button as="button" variant="secondary" class="mt-2 w-24" @click="backToList">
          {{ t('components.buttons.back') }}
        </Button>
      </div>
    </LoadingOverlay>
  </div>
</template>

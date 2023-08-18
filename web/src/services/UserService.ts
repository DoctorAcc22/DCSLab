import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import route, { Config } from "ziggy-js";
import { User } from "../types/models/User";
import { Resource } from "../types/resources/Resource";
import { Collection } from "../types/resources/Collection";
import { ServiceResponse } from "../types/services/ServiceResponse";
import { AxiosError, AxiosResponse, isAxiosError } from "axios";
import ErrorHandlerService from "./ErrorHandlerService";
import { SearchFormFieldValues } from "../types/forms/SearchFormFieldValues";
import { UserFormFieldValues } from "../types/forms/UserFormFieldValues";
import { StatusCode } from "../types/enums/StatusCode";

export default class UserService {
    private ziggyRoute: Config;
    private ziggyRouteStore = useZiggyRouteStore();

    private errorHandlerService;

    constructor() {
        this.ziggyRoute = this.ziggyRouteStore.getZiggy;

        this.errorHandlerService = new ErrorHandlerService();
    }

    public async create(payload: UserFormFieldValues): Promise<ServiceResponse<User | null>> {
        const result: ServiceResponse<User | null> = {
            success: false,
        }

        try {
            const url = route('api.post.db.admin.user.save', undefined, false, this.ziggyRoute);
            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            const response: AxiosResponse<User> = await axios.post(url, payload);

            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            result.success = true;
            result.data = response.data;

            return result;
        } catch (e: unknown) {
            if (e instanceof Error && e.message.includes('Ziggy error')) {
                return this.errorHandlerService.generateZiggyUrlErrorServiceResponse(e.message);
            } else if (isAxiosError(e)) {
                return this.errorHandlerService.generateAxiosErrorServiceResponse(e as AxiosError);
            } else {
                return result;
            }
        }
    }

    public async readAny(args: SearchFormFieldValues): Promise<ServiceResponse<Collection<Array<User>> | Resource<Array<User>> | null>> {
        const result: ServiceResponse<Collection<Array<User>> | Resource<Array<User>> | null> = {
            success: false,
        }

        try {
            const queryParams: Record<string, string | number | boolean> = {};
            queryParams['search'] = args.search ? args.search : '';
            queryParams['refresh'] = args.refresh;
            queryParams['paginate'] = args.paginate;
            if (args.page) queryParams['page'] = args.page;
            if (args.per_page) queryParams['per_page'] = args.per_page;

            //ZiggyRouteNotFoundException
            //const url = route('invalid.route', undefined, false, this.ziggyRoute);
            const url = route('api.get.db.admin.user.read_any', { _query: queryParams }, false, this.ziggyRoute);

            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            const response: AxiosResponse<Collection<Array<User>>> = await axios.get(url);

            //Slow API Call (10 seconds Delay)
            /*
            await new Promise((resolve) => {
                setTimeout(resolve, 10000);
            });
            console.log('Slow API Call (10 Seconds Delay)');
            */

            if (response != null) {
                result.success = true;
                result.data = response.data;
            }

            return result;
        } catch (e: unknown) {
            if (e instanceof Error && e.message.includes('Ziggy error')) {
                return this.errorHandlerService.generateZiggyUrlErrorServiceResponse(e.message);
            } else if (isAxiosError(e)) {
                if (e.response) {
                    switch (e.response.status) {
                        case StatusCode.UnprocessableEntity:
                            return this.errorHandlerService.generateAxiosValidationErrorServiceResponse(e as AxiosError);
                        default:
                            return this.errorHandlerService.generateAxiosErrorServiceResponse(e as AxiosError);
                    }
                } else {
                    return result;
                }
            } else {
                return result;
            }
        }
    }

    public async read(ulid: string): Promise<ServiceResponse<User | null>> {
        const result: ServiceResponse<User | null> = {
            success: false,
        }

        try {
            const url = route('api.get.db.admin.user.read', {
                user: ulid
            }, false, this.ziggyRoute);

            const response: AxiosResponse<Resource<User>> = await axios.get(url);

            result.success = true;
            result.data = response.data.data;

            return result;
        } catch (e: unknown) {
            if (e instanceof Error && e.message.includes('Ziggy error')) {
                return this.errorHandlerService.generateZiggyUrlErrorServiceResponse(e.message);
            } else if (isAxiosError(e)) {
                return this.errorHandlerService.generateAxiosErrorServiceResponse(e as AxiosError);
            } else {
                return result;
            }
        }
    }

    public async edit(ulid: string, payload: UserFormFieldValues): Promise<ServiceResponse<User | null>> {
        const result: ServiceResponse<User | null> = {
            success: false,
        }

        try {
            const url = route('api.post.db.admin.user.edit', ulid, false, this.ziggyRoute);
            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            const response: AxiosResponse<User> = await axios.post(url, payload);

            result.success = true;
            result.data = response.data;

            return result;
        } catch (e: unknown) {
            if (e instanceof Error && e.message.includes('Ziggy error')) {
                return this.errorHandlerService.generateZiggyUrlErrorServiceResponse(e.message);
            } else if (isAxiosError(e)) {
                return this.errorHandlerService.generateAxiosErrorServiceResponse(e as AxiosError);
            } else {
                return result;
            }
        }
    }

    public async getTokensCount(ulid: string): Promise<ServiceResponse<number | null>> {
        const result: ServiceResponse<number | null> = {
            success: false,
        }

        try {
            const url = route('api.get.db.admin.user.read.tokens.count', {
                user: ulid
            }, false, this.ziggyRoute);

            const response: AxiosResponse<Resource<number>> = await axios.get(url);

            result.success = true;
            result.data = response.data.data;

            return result;
        } catch (e: unknown) {
            if (e instanceof Error && e.message.includes('Ziggy error')) {
                return this.errorHandlerService.generateZiggyUrlErrorServiceResponse(e.message);
            } else if (isAxiosError(e)) {
                return this.errorHandlerService.generateAxiosErrorServiceResponse(e as AxiosError);
            } else {
                return result;
            }
        }
    }
}
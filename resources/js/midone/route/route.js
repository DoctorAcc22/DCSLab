import { createRouter, createWebHistory } from 'vue-router';

import SideMenu from '../layouts/side-menu/Main.vue';

import MainDashboard from '../views/dashboard/MainDashboard.vue';
import AdminUsers from "../views/administrators/Users";

import DBBackup from "../views/dev/DBBackup";
import Ex1 from "../views/dev/Ex1";
import Ex2 from "../views/dev/Ex2";

const root = '/dashboard';

const routes = [
    {
        path: root,
        component: SideMenu,
        children: [
            {
                path: root,
                name: 'side-menu-dashboard-maindashboard',
                component: MainDashboard,
                meta: { remember: false }
            },
        ],
    },
    {
        path: root + '/admin',
        component: SideMenu,
        children: [
            {
                path: root + '/admin' + '/users',
                name: 'side-menu-administrators-users',
                component: AdminUsers,
                meta: { remember: true }
            }
        ],
    },
    {
        path: root + '/dev',
        component: SideMenu,
        children: [
            {
                path: root + '/dev' + '/tools/db_backup',
                name: 'side-menu-devtools-backup',
                component: DBBackup,
                meta: { remember: true }
            }
        ],
    },
    {
        path: root + '/dev' + '/examples',
        name: 'side-menu-devtools-examples',
        component: SideMenu,
        children: [
            {
                path: root + '/dev' + '/examples' + '/ex1',
                name: 'side-menu-devtools-examples-ex1',
                component: Ex1,
                meta: { remember: true }
            },
            {
                path: root + '/dev' + '/examples' + '/ex2',
                name: 'side-menu-devtools-examples-ex2',
                component: Ex2,
                meta: { remember: true }
            }
        ]
    },
];

const router = createRouter({
    history: createWebHistory(process.env.BASE_URL),
    routes,
});

router.afterEach((to, from) => {
    axios.post('/api/post/dashboard/core/log/route', {
        to: to.name,
        params: to.params
    });

    if (to.matched.some(r => r.meta.remember))
        localStorage.setItem('DCSLAB_LAST_ROUTE', to.name);
});

export default router;

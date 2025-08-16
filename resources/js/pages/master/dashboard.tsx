import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';

interface MasterDashboardProps {
    user?: {
        name: string;
        email: string;
        role: string;
    };
    stats?: {
        total_companies: number;
        active_companies: number;
        trial_companies: number;
        suspended_companies?: number;
        expired_companies?: number;
    };
}

export default function MasterDashboard({ user, stats }: MasterDashboardProps) {
    // Provide default values if props are missing
    const userData = user || { name: 'Admin User', email: '', role: 'admin' };
    const statsData = stats || { 
        total_companies: 0, 
        active_companies: 0, 
        trial_companies: 0,
        suspended_companies: 0,
        expired_companies: 0
    };
    
    return (
        <AppLayout>
            <Head title="Master Dashboard" />
            
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <h1 className="text-2xl font-bold mb-4">
                                Welcome, {userData.name}
                            </h1>
                            <p className="mb-6">System Administrator Dashboard</p>
                            
                            <div className="grid grid-cols-1 md:grid-cols-5 gap-6">
                                <div className="bg-blue-100 p-4 rounded-lg">
                                    <h3 className="font-semibold text-blue-800">Total Companies</h3>
                                    <p className="text-2xl font-bold text-blue-600">{statsData.total_companies}</p>
                                </div>
                                
                                <div className="bg-green-100 p-4 rounded-lg">
                                    <h3 className="font-semibold text-green-800">Active Companies</h3>
                                    <p className="text-2xl font-bold text-green-600">{statsData.active_companies}</p>
                                </div>
                                
                                <div className="bg-yellow-100 p-4 rounded-lg">
                                    <h3 className="font-semibold text-yellow-800">Trial Companies</h3>
                                    <p className="text-2xl font-bold text-yellow-600">{statsData.trial_companies}</p>
                                </div>
                                
                                <div className="bg-orange-100 p-4 rounded-lg">
                                    <h3 className="font-semibold text-orange-800">Suspended</h3>
                                    <p className="text-2xl font-bold text-orange-600">{statsData.suspended_companies || 0}</p>
                                </div>
                                
                                <div className="bg-red-100 p-4 rounded-lg">
                                    <h3 className="font-semibold text-red-800">Expired</h3>
                                    <p className="text-2xl font-bold text-red-600">{statsData.expired_companies || 0}</p>
                                </div>
                            </div>
                            
                            <div className="mt-8">
                                <h2 className="text-xl font-semibold mb-4">Quick Actions</h2>
                                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                    <button className="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                                        Manage Companies
                                    </button>
                                    <button className="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg">
                                        View Reports
                                    </button>
                                    <button className="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg">
                                        System Settings
                                    </button>
                                    <button className="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg">
                                        User Management
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
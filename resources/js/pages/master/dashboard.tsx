import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';

interface MasterDashboardProps {
    user: {
        name: string;
        email: string;
        role: string;
    };
    stats: {
        total_companies: number;
        active_companies: number;
        trial_companies: number;
    };
}

export default function MasterDashboard({ user, stats }: MasterDashboardProps) {
    return (
        <AppLayout>
            <Head title="Master Dashboard" />
            
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <h1 className="text-2xl font-bold mb-4">
                                Welcome, {user.name}
                            </h1>
                            <p className="mb-6">System Administrator Dashboard</p>
                            
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div className="bg-blue-100 p-4 rounded-lg">
                                    <h3 className="font-semibold text-blue-800">Total Companies</h3>
                                    <p className="text-2xl font-bold text-blue-600">{stats.total_companies}</p>
                                </div>
                                
                                <div className="bg-green-100 p-4 rounded-lg">
                                    <h3 className="font-semibold text-green-800">Active Companies</h3>
                                    <p className="text-2xl font-bold text-green-600">{stats.active_companies}</p>
                                </div>
                                
                                <div className="bg-yellow-100 p-4 rounded-lg">
                                    <h3 className="font-semibold text-yellow-800">Trial Companies</h3>
                                    <p className="text-2xl font-bold text-yellow-600">{stats.trial_companies}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
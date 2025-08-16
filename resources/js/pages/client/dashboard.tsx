import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';

interface ClientDashboardProps {
    user: {
        first_name: string;
        last_name: string;
        email: string;
        role: string;
    };
    company: {
        id: number;
        code: string;
    };
}

export default function ClientDashboard({ user, company }: ClientDashboardProps) {
    return (
        <AppLayout>
            <Head title="Dashboard" />
            
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <h1 className="text-2xl font-bold mb-4">
                                Welcome, {user.first_name} {user.last_name}
                            </h1>
                            <p className="mb-6">
                                Company: {company.code} | Role: {user.role}
                            </p>
                            
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div className="bg-blue-100 p-4 rounded-lg">
                                    <h3 className="font-semibold text-blue-800">Today's Sales</h3>
                                    <p className="text-2xl font-bold text-blue-600">₱0.00</p>
                                </div>
                                
                                <div className="bg-green-100 p-4 rounded-lg">
                                    <h3 className="font-semibold text-green-800">Products</h3>
                                    <p className="text-2xl font-bold text-green-600">0</p>
                                </div>
                                
                                <div className="bg-purple-100 p-4 rounded-lg">
                                    <h3 className="font-semibold text-purple-800">Transactions</h3>
                                    <p className="text-2xl font-bold text-purple-600">0</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
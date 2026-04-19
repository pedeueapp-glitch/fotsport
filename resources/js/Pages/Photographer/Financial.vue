<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import PhotographerLayout from '@/Layouts/PhotographerLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref, computed } from 'vue';

import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
  LineElement,
  PointElement,
  Filler
} from 'chart.js';
import { Bar, Line } from 'vue-chartjs';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend, LineElement, PointElement, Filler);

const props = defineProps({
    balance: Number,
    pix_key: String,
    document: String,
    withdrawals: Array,
    sales: {
        type: Array,
        default: () => []
    },
    withdrawal_fee_percentage: Number
});

const pixForm = useForm({
    pix_key: props.pix_key || '',
    document: props.document || ''
});

const withdrawForm = useForm({
    amount: ''
});

const savePix = () => {
    pixForm.post(route('financial.updatePix'), {
        preserveScroll: true
    });
};

const requestWithdraw = () => {
    withdrawForm.post(route('financial.withdraw'), {
        preserveScroll: true,
        onSuccess: () => withdrawForm.reset('amount')
    });
};

const timeFilter = ref('day');

const chartData = computed(() => {
    const grouped = {};
    const sortedSales = [...props.sales].sort((a, b) => new Date(a.created_at) - new Date(b.created_at));

    sortedSales.forEach(sale => {
        let dateObj = new Date(sale.created_at);
        let key = '';
        if (timeFilter.value === 'day') {
            key = dateObj.toLocaleDateString('pt-BR');
        } else if (timeFilter.value === 'week') {
            let tempDate = new Date(sale.created_at);
            let startOfWeek = tempDate.getDate() - tempDate.getDay();
            let weekDate = new Date(tempDate.setDate(startOfWeek));
            key = 'Sem ' + weekDate.toLocaleDateString('pt-BR').substring(0, 5);
        } else if (timeFilter.value === 'month') {
            const m = dateObj.toLocaleString('pt-BR', { month: 'short' });
            key = m.toUpperCase() + '/' + dateObj.getFullYear();
        }
        
        if (!grouped[key]) grouped[key] = 0;
        grouped[key] += Number(sale.amount);
    });
    
    const labels = Object.keys(grouped);
    const data = labels.map(l => grouped[l]);

    return {
        labels: labels,
        datasets: [
            {
                label: 'Volume de Vendas (R$)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)', 
                borderColor: '#3b82f6',
                borderWidth: 3,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#3b82f6',
                pointHoverRadius: 6,
                data: data,
                fill: true,
                tension: 0.4
            }
        ]
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: '#1a1a1a',
            padding: 12,
            titleFont: { size: 14, weight: 'bold' },
            bodyFont: { size: 13 },
            callbacks: {
                label: function(context) {
                    let label = context.dataset.label || '';
                    if (label) label += ': ';
                    if (context.parsed.y !== null) {
                        label += new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(context.parsed.y);
                    }
                    return label;
                }
            }
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            grid: { display: false },
            ticks: {
                callback: function(value) {
                    return 'R$ ' + value;
                }
            }
        },
        x: {
            grid: { display: false }
        }
    }
};

const totalSalesVolume = computed(() => {
    return props.sales.reduce((sum, sale) => sum + Number(sale.amount), 0);
});
</script>

<template>
    <Head title="Meu Financeiro" />

    <PhotographerLayout title="Financeiro" subtitle="Gerencie seus ganhos e solicite pagamentos.">
        <div class="space-y-12">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Carteira -->
                <div class="lg:col-span-2 bg-brand-dark rounded-xl p-8 text-white shadow-xl relative overflow-hidden group">
                    <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-brand-blue/10 blur-3xl group-hover:bg-brand-blue/20 transition-all duration-700"></div>
                    
                    <div class="relative z-10">
                        <span class="text-[9px] font-black uppercase tracking-[0.4em] text-brand-orange mb-2 block text-opacity-70">Saldo Disponível</span>
                        <div class="text-5xl font-black mb-10 tracking-tighter">
                            R$ {{ balance.toFixed(2) }}
                        </div>
                        
                        <div class="max-w-md bg-white/5 backdrop-blur-md border border-white/10 p-6 rounded-xl">
                            <h4 class="font-black text-[9px] uppercase tracking-widest text-white/50 mb-4">Solicitar Saque (Mínimo R$ 5,00)</h4>
                            <form @submit.prevent="requestWithdraw" class="flex items-center gap-3">
                                <div class="relative flex-grow">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-brand-orange font-black">R$</span>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="5"
                                        v-model="withdrawForm.amount"
                                        class="w-full pl-10 pr-4 py-3 bg-white text-brand-dark font-black text-lg rounded-lg border-0 focus:ring-4 focus:ring-brand-blue/30 transition-all"
                                        placeholder="0,00"
                                        required
                                    />
                                </div>
                                <button :disabled="withdrawForm.processing" class="px-6 py-3.5 bg-brand-orange hover:bg-brand-orange-hover text-white font-black uppercase tracking-widest text-[9px] rounded-lg transition-all active:scale-95 disabled:opacity-50">
                                    Sacar
                                </button>
                            </form>
                            <p class="text-[8px] text-white/40 mt-3 font-medium uppercase tracking-widest">A plataforma retém {{ withdrawal_fee_percentage }}% de taxa operacional.</p>
                        </div>
                    </div>
                </div>

                <!-- Dados PIX -->
                <div class="bg-gray-50 rounded-xl p-8 border border-gray-100 flex flex-col relative overflow-hidden group">
                    <div class="relative z-10 h-full flex flex-col">
                        <h3 class="text-sm font-black text-brand-dark uppercase tracking-widest mb-6">Recebimento</h3>
                        
                        <form @submit.prevent="savePix" class="space-y-4 flex-grow">
                            <div>
                                <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Chave PIX</label>
                                <input
                                    type="text"
                                    class="w-full px-4 py-3 bg-white border border-gray-200 rounded-lg text-xs font-bold focus:border-brand-blue focus:ring-4 focus:ring-brand-blue/10 transition-all"
                                    v-model="pixForm.pix_key"
                                    placeholder="Chave PIX"
                                    required
                                />
                            </div>

                            <div>
                                <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Documento</label>
                                <input
                                    type="text"
                                    class="w-full px-4 py-3 bg-white border border-gray-200 rounded-lg text-xs font-bold focus:border-brand-blue focus:ring-4 focus:ring-brand-blue/10 transition-all"
                                    v-model="pixForm.document"
                                    placeholder="CPF/CNPJ"
                                    required
                                />
                            </div>

                            <div class="pt-2">
                                <button :disabled="pixForm.processing" class="w-full py-3.5 bg-brand-blue hover:bg-brand-blue-hover text-white font-black text-[9px] uppercase tracking-[0.2em] rounded-lg transition-all active:scale-95 disabled:opacity-50">
                                    Salvar Dados
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Gráfico -->
            <div class="bg-white rounded-xl p-8 border border-gray-100 shadow-sm overflow-hidden relative">
                <div class="flex flex-col md:flex-row justify-between md:items-center mb-8 gap-6 relative z-10">
                    <div>
                        <h3 class="text-lg font-black text-brand-dark uppercase tracking-widest">Vendas</h3>
                        <p class="text-[10px] text-gray-400 font-medium">Volume total de vendas no período.</p>
                    </div>
                    
                    <div class="flex bg-gray-50 p-1.5 rounded-lg border border-gray-100">
                        <button v-for="t in ['day', 'week', 'month']" :key="t"
                                @click="timeFilter = t" 
                                :class="timeFilter === t ? 'bg-white shadow-sm text-brand-blue' : 'text-gray-400 hover:text-brand-dark'" 
                                class="px-4 py-2 text-[9px] font-black uppercase tracking-widest rounded-md transition-all">
                            {{ t === 'day' ? 'Dia' : (t === 'week' ? 'Sem' : 'Mês') }}
                        </button>
                    </div>
                </div>

                <div class="h-[300px] w-full relative">
                    <Line v-if="chartData.labels.length > 0" :data="chartData" :options="chartOptions" />
                    <div v-else class="absolute inset-0 flex items-center justify-center text-gray-200 font-black text-[10px] uppercase tracking-widest bg-gray-50/50 rounded-xl">
                        Aguardando vendas...
                    </div>
                </div>
            </div>

            <!-- Tabela -->
            <div class="bg-gray-50 rounded-xl p-8 border border-gray-100 overflow-hidden">
                <h3 class="text-sm font-black text-brand-dark uppercase tracking-widest mb-8 text-center md:text-left">Extrato de Saques</h3>

                <div v-if="withdrawals.length === 0" class="py-12 text-center">
                    <p class="text-[9px] font-black text-gray-300 uppercase tracking-[0.3em]">Sem movimentações</p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="text-[8px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-200">
                                <th class="pb-4 pl-2">Data</th>
                                <th class="pb-4">Bruto</th>
                                <th class="pb-4">Taxa</th>
                                <th class="pb-4">Líquido</th>
                                <th class="pb-4 pr-2">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="withdrawal in withdrawals" :key="withdrawal.id" class="group hover:bg-white transition-colors">
                                <td class="py-5 pl-2 font-bold text-gray-400 group-hover:text-brand-dark">
                                    {{ new Date(withdrawal.created_at).toLocaleDateString('pt-BR') }}
                                </td>
                                <td class="py-5 font-black text-brand-dark">R$ {{ Number(withdrawal.request_amount).toFixed(2) }}</td>
                                <td class="py-5 font-black text-red-400">- R$ {{ Number(withdrawal.fee_amount).toFixed(2) }}</td>
                                <td class="py-5 font-black text-brand-blue">R$ {{ Number(withdrawal.net_amount).toFixed(2) }}</td>
                                <td class="py-5 pr-2">
                                    <span class="px-3 py-1.5 inline-flex text-[8px] font-black uppercase tracking-widest rounded-md"
                                        :class="{
                                            'bg-yellow-100 text-yellow-700': withdrawal.status === 'pending',
                                            'bg-green-100 text-green-700': withdrawal.status === 'approved',
                                            'bg-red-100 text-red-700': withdrawal.status === 'rejected',
                                        }">
                                        {{ withdrawal.status === 'pending' ? 'Pendente' : (withdrawal.status === 'approved' ? '✓ Pago' : '✖ Recusado') }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </PhotographerLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
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

    <div class="min-h-screen bg-white flex flex-col font-sans text-brand-dark">
        <Navbar />

        <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 w-full">
            <!-- Header -->
            <header class="mb-20">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-8">
                    <div class="max-w-2xl">
                        <div class="inline-flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 bg-brand-orange/10 rounded-xl flex items-center justify-center text-brand-orange">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <span class="text-[10px] font-black text-brand-orange uppercase tracking-[.4em]">Gestão Financeira</span>
                        </div>
                        <h1 class="text-4xl md:text-6xl font-black text-brand-dark uppercase tracking-tighter mb-4">
                            Meu <span class="text-brand-blue">Saldo</span>
                        </h1>
                        <p class="text-gray-500 text-lg font-medium">
                            Acompanhe suas vendas, gerencie seu saldo e solicite saques de forma simples.
                        </p>
                    </div>
                </div>
                <div class="mt-10 h-1.5 w-20 bg-brand-orange rounded-full"></div>
            </header>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-10 mb-16">
                <!-- Carteira -->
                <div class="lg:col-span-2 bg-brand-dark rounded-[3rem] p-12 text-white shadow-2xl relative overflow-hidden group">
                    <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-brand-blue/10 blur-3xl group-hover:bg-brand-blue/20 transition-all duration-700"></div>
                    
                    <div class="relative z-10">
                        <span class="text-[10px] font-black uppercase tracking-[0.4em] text-brand-orange mb-4 block">Saldo Disponível</span>
                        <div class="text-6xl md:text-8xl font-black mb-8 tracking-tighter">
                            R$ {{ balance.toFixed(2) }}
                        </div>
                        
                        <div class="max-w-md bg-white/5 backdrop-blur-md border border-white/10 p-8 rounded-[2rem]">
                            <h4 class="font-black text-xs uppercase tracking-widest text-white/50 mb-6">Solicitar Saque</h4>
                            <form @submit.prevent="requestWithdraw" class="flex flex-col sm:flex-row gap-4">
                                <div class="relative flex-grow">
                                    <span class="absolute left-6 top-1/2 -translate-y-1/2 text-brand-orange font-black text-xl">R$</span>
                                    <input
                                        type="number"
                                        step="0.01"
                                        v-model="withdrawForm.amount"
                                        class="w-full pl-16 pr-6 py-5 bg-white text-brand-dark font-black text-2xl rounded-2xl border-0 focus:ring-4 focus:ring-brand-blue/30 transition-all"
                                        placeholder="0,00"
                                        required
                                    />
                                </div>
                                <button :disabled="withdrawForm.processing" class="px-10 py-5 bg-brand-orange hover:bg-brand-orange-hover text-white font-black uppercase tracking-widest text-xs rounded-2xl transition-all active:scale-95 disabled:opacity-50 shadow-xl shadow-brand-orange/20">
                                    Sacar
                                </button>
                            </form>
                            <p class="text-[10px] text-white/40 mt-4 font-medium uppercase tracking-widest">A plataforma retém {{ withdrawal_fee_percentage }}% de taxa operacional.</p>
                            
                            <div v-if="withdrawForm.errors.amount" class="mt-4 p-4 bg-red-500/20 text-red-200 text-xs font-bold rounded-xl border border-red-500/30">
                                {{ withdrawForm.errors.amount }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dados PIX -->
                <div class="bg-gray-50 rounded-[3rem] p-12 border border-gray-100 flex flex-col relative overflow-hidden group">
                    <div class="absolute -bottom-24 -right-24 w-64 h-64 bg-brand-blue/5 rounded-full blur-3xl group-hover:bg-brand-blue/10 transition-all duration-700"></div>

                    <div class="relative z-10 flex flex-col h-full">
                        <div class="flex items-center gap-4 mb-10">
                            <div class="w-12 h-12 bg-white rounded-2xl shadow-xl flex items-center justify-center text-brand-orange rotate-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            </div>
                            <h3 class="text-xl font-black text-brand-dark uppercase tracking-tighter">Dados de <span class="text-brand-orange">Recebimento</span></h3>
                        </div>
                        
                        <form @submit.prevent="savePix" class="space-y-6 flex-grow">
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Sua Chave PIX</label>
                                <input
                                    type="text"
                                    class="w-full px-6 py-4 bg-white border border-gray-200 rounded-2xl text-sm font-bold focus:border-brand-blue focus:ring-4 focus:ring-brand-blue/10 transition-all"
                                    v-model="pixForm.pix_key"
                                    placeholder="E-mail, CPF ou Celular"
                                    required
                                />
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">CPF/CNPJ do Titular</label>
                                <input
                                    type="text"
                                    class="w-full px-6 py-4 bg-white border border-gray-200 rounded-2xl text-sm font-bold focus:border-brand-blue focus:ring-4 focus:ring-brand-blue/10 transition-all"
                                    v-model="pixForm.document"
                                    placeholder="000.000.000-00"
                                    required
                                />
                            </div>

                            <div class="pt-4">
                                <button :disabled="pixForm.processing" class="w-full py-5 bg-brand-blue hover:bg-brand-blue-hover text-white font-black text-xs uppercase tracking-[0.2em] rounded-2xl shadow-xl shadow-brand-blue/20 transition-all active:scale-95 disabled:opacity-50">
                                    Atualizar Dados
                                </button>
                                <Transition
                                    enter-active-class="transition duration-300"
                                    enter-from-class="opacity-0 translate-y-2"
                                    enter-to-class="opacity-100 translate-y-0"
                                >
                                    <p v-if="pixForm.recentlySuccessful" class="text-[10px] font-black text-green-500 text-center mt-4 uppercase tracking-widest">
                                        ✓ Salvo com sucesso
                                    </p>
                                </Transition>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Gráfico e Métricas -->
            <div class="bg-white rounded-[3rem] p-12 border border-gray-100 shadow-sm mb-16 overflow-hidden relative">
                <div class="flex flex-col md:flex-row justify-between md:items-center mb-12 gap-8 relative z-10">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <div class="h-1 w-4 bg-brand-blue rounded-full"></div>
                            <span class="text-[10px] font-black text-brand-blue uppercase tracking-widest">Performance</span>
                        </div>
                        <h3 class="text-3xl font-black text-brand-dark uppercase tracking-tighter">Histórico de <span class="text-brand-blue">Vendas</span></h3>
                    </div>
                    
                    <div class="flex bg-gray-50 p-2 rounded-2xl border border-gray-100">
                        <button v-for="t in ['day', 'week', 'month']" :key="t"
                                @click="timeFilter = t" 
                                :class="timeFilter === t ? 'bg-white shadow-xl text-brand-blue scale-105' : 'text-gray-400 hover:text-brand-dark'" 
                                class="px-6 py-3 text-[10px] font-black uppercase tracking-widest rounded-xl transition-all">
                            {{ t === 'day' ? 'Dia' : (t === 'week' ? 'Semana' : 'Mês') }}
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
                    <div class="p-8 bg-gray-50 rounded-[2rem] border border-gray-100 flex flex-col justify-center">
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Vendas Totais</span>
                        <div class="text-4xl font-black text-brand-dark tracking-tighter">{{ sales.length }} <span class="text-xs text-gray-300 font-medium">un.</span></div>
                    </div>
                    <div class="md:col-span-3 p-8 bg-brand-blue/5 rounded-[2rem] border border-brand-blue/10 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-black text-brand-blue uppercase tracking-widest mb-2 block">Volume Total Gerado</span>
                            <div class="text-5xl font-black text-brand-blue tracking-tighter">R$ {{ totalSalesVolume.toFixed(2) }}</div>
                        </div>
                        <div class="hidden sm:flex w-20 h-20 bg-white rounded-3xl items-center justify-center text-brand-blue shadow-xl rotate-6">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                        </div>
                    </div>
                </div>

                <div class="h-[400px] w-full relative">
                    <template v-if="chartData.labels.length > 0">
                        <Line :data="chartData" :options="chartOptions" />
                    </template>
                    <div v-else class="absolute inset-0 flex items-center justify-center text-gray-300 font-black text-xs uppercase tracking-[.3em] border-4 border-dashed border-gray-50 rounded-[2.5rem] bg-gray-50/50">
                        Aguardando primeiras vendas...
                    </div>
                </div>
            </div>

            <!-- Tabela de Saques -->
            <div class="bg-gray-50 rounded-[3.5rem] p-12 md:p-20 border border-gray-100 overflow-hidden">
                <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-8 mb-12">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <div class="h-1 w-4 bg-brand-orange rounded-full"></div>
                            <span class="text-[10px] font-black text-brand-orange uppercase tracking-widest">Logs de Transações</span>
                        </div>
                        <h3 class="text-3xl font-black text-brand-dark uppercase tracking-tighter">Extrato de <span class="text-brand-orange">Saques</span></h3>
                    </div>
                </header>

                <div v-if="withdrawals.length === 0" class="py-20 text-center flex flex-col items-center">
                    <div class="w-20 h-20 bg-white rounded-3xl shadow-xl flex items-center justify-center text-gray-200 mb-6">
                         <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.3em]">Nenhuma movimentação realizada ainda</p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-200">
                                <th class="pb-6 pl-4">Data da Solicitação</th>
                                <th class="pb-6">Valor Bruto</th>
                                <th class="pb-6">Taxa ({{ withdrawal_fee_percentage }}%)</th>
                                <th class="pb-6">Total Líquido</th>
                                <th class="pb-6 pr-4">Status do Saque</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="withdrawal in withdrawals" :key="withdrawal.id" class="group hover:bg-white transition-colors">
                                <td class="py-8 pl-4 font-bold text-sm text-gray-500 group-hover:text-brand-dark transition-colors">
                                    {{ new Date(withdrawal.created_at).toLocaleDateString('pt-BR') }}
                                </td>
                                <td class="py-8 font-black text-brand-dark">R$ {{ Number(withdrawal.request_amount).toFixed(2) }}</td>
                                <td class="py-8 font-black text-red-500">- R$ {{ Number(withdrawal.fee_amount).toFixed(2) }}</td>
                                <td class="py-8 font-black text-brand-blue text-xl">R$ {{ Number(withdrawal.net_amount).toFixed(2) }}</td>
                                <td class="py-8 pr-4">
                                    <span class="px-5 py-2.5 inline-flex text-[10px] font-black uppercase tracking-widest rounded-xl shadow-sm"
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
        </main>

        <Footer />
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&display=swap');

.font-sans {
    font-family: 'Inter', sans-serif;
}
</style>
